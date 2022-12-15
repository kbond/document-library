<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Contracts\Service\ServiceProviderInterface;
use Zenstruck\Document\Attribute\UploadedFile;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 *
 * @internal
 */
if (\interface_exists(ValueResolverInterface::class)) {
    class PendingDocumentValueResolver implements ValueResolverInterface
    {
        public function __construct(
            /** @var ServiceProviderInterface<RequestFilesExtractor> $locator */
            private ServiceProviderInterface $locator
        ) {
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
                $this->extractor()->extractFilesFromRequest(
                    $request,
                    $path,
                    PendingDocument::class !== $argument->getType()
                ),
            ];
        }

        private function extractor(): RequestFilesExtractor
        {
            return $this->locator->get(RequestFilesExtractor::class);
        }
    }
} else {
    class PendingDocumentValueResolver implements ArgumentValueResolverInterface
    {
        public function __construct(
            /** @var ServiceProviderInterface<RequestFilesExtractor> $locator */
            private ServiceProviderInterface $locator
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
            \assert(!empty($attributes));

            $path = $attributes[0]?->path
                ?? $argument->getName();

            return [
                $this->extractor()->extractFilesFromRequest(
                    $request,
                    $path,
                    PendingDocument::class !== $argument->getType()
                ),
            ];
        }

        private function extractor(): RequestFilesExtractor
        {
            return $this->locator->get(RequestFilesExtractor::class);
        }
    }
}
