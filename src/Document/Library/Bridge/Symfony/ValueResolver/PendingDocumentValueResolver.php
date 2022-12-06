<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\ValueResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 *
 * @internal
 */
class PendingDocumentValueResolver implements ArgumentValueResolverInterface, ValueResolverInterface
{
    public function __construct(
        private RequestFilesExtractor $filesExtractor
    )
    {
    }

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

        return [
            $this->filesExtractor->extractFilesFromRequest(
                $request,
                $path,
                $argument->getType() !== PendingDocument::class
            )
        ];
    }
}

