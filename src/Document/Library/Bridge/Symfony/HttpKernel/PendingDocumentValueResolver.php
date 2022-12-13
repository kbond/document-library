<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Zenstruck\Document\Attribute\UploadedFile;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 *
 * @internal
 */
class PendingDocumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private RequestFilesExtractor $filesExtractor
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return PendingDocument::class === $argument->getType()
            || !empty($argument->getAttributes(UploadedFile::class));
    }

    /**
     * @return iterable<PendingDocument|array|null>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attributes = $argument->getAttributes(UploadedFile::class);

        if (
            empty($attributes)
            && PendingDocument::class !== $argument->getType()
        ) {
            return [];
        }

        $path = $attributes[0]?->path
            ?? $argument->getName();

        return [
            $this->filesExtractor->extractFilesFromRequest(
                $request,
                $path,
                PendingDocument::class !== $argument->getType()
            ),
        ];
    }
}
