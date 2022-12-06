<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\ValueResolver;

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

        return $argument->getType() === PendingDocument::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PendingDocument::class) {
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

        $file = $propertyAccessor->getValue($request->files->all(), $path);

        if (!$file) {
            return [null];
        }

        return [new PendingDocument($file)];
    }
}

