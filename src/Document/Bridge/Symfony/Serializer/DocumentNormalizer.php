<?php

namespace Zenstruck\Document\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Zenstruck\Document;
use Zenstruck\Document\File\LazyFile;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\SerializableDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public const LIBRARY = 'library';
    public const METADATA = 'metadata';

    public function __construct(private LibraryRegistry $registry)
    {
    }

    /**
     * @param Document $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string|array
    {
        if ($metadata = $context[self::METADATA] ?? null) {
            return (new SerializableDocument($object, $metadata))->serialize();
        }

        return $object->path();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Document;
    }

    /**
     * @param string $data
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Document
    {
        $document = new LazyFile($data);

        if ($library = $context[self::LIBRARY] ?? null) {
            $document->setLibrary($this->registry->get($library));
        }

        return $document;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Document::class === $type;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
