<?php

namespace Zenstruck\Document\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Zenstruck\Document;
use Zenstruck\Document\File\LazyFile;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public const LIBRARY = 'library';

    public function __construct(private LibraryRegistry $registry)
    {
    }

    /**
     * @param Document $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
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
        if (!$library = $context[self::LIBRARY]) {
            throw new UnexpectedValueException('library context is required'); // todo
        }

        return new LazyFile($data, $this->registry->get($library));
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_string($data) && Document::class === $type;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
