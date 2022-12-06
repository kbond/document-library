<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\ValueResolver;

use Symfony\Component\HttpFoundation\File\UploadedFile as SfUploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 *
 * @internal
 */
class PendingDocumentValueResolver implements ArgumentValueResolverInterface, ValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        @trigger_deprecation('symfony/http-kernel', '6.2', 'The "%s()" method is deprecated, use "resolve()" instead.', __METHOD__);

        return in_array(
            $argument->getType(),
            [PendingDocument::class, 'array'],
            true
        );
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (
            !in_array(
                $argument->getType(),
                [PendingDocument::class, 'array'],
                true
            )
        ) {
            return [];
        }

        $path = $argument->getAttributesOfType(UploadedFile::class)[0]?->path
            ?? $argument->getName();

        // Convert HTML paths, like "data[file]", to
        // PropertyAccessor compatible ("[data][file]")
        if ($path[0] !== '[') {
            $path = preg_replace(
                '/^([^[]+)/',
                '[$1]',
                $path
            );
        }

        $propertyAccessor = new PropertyAccessor(
            PropertyAccessor::DISALLOW_MAGIC_METHODS,
            PropertyAccessor::THROW_ON_INVALID_PROPERTY_PATH
        );

        $files = $propertyAccessor->getValue($request->files->all(), $path);

        if (is_array($files)) {
            if ($argument->getType() === PendingDocument::class) {
                throw new \LogicException(sprintf('Could not resolve the "%s $%s" controller argument: expecting a single file, got %d files.', $argument->getType(), $argument->getName(), count($files)));
            }

            return [
                array_map(
                    static fn (SfUploadedFile $file) => new PendingDocument($file),
                    $files
                )
            ];
        }

        if (!$files) {
            return [null];
        }

        if ($argument->getType() === PendingDocument::class) {
            return [new PendingDocument($files)];
        }

        return [[new PendingDocument($files)]];
    }
}

