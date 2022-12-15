<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer;
use Zenstruck\Document\SerializableDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DocumentNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public const LIBRARY = 'library';
    public const METADATA = 'metadata';
    public const RENAME = 'rename';

    public function __construct(private LibraryRegistry $registry, private Namer $namer)
    {
    }

    /**
     * @param Document $object
     */
    final public function normalize(mixed $object, ?string $format = null, array $context = []): string|array
    {
        if ($metadata = $context[self::METADATA] ?? false) {
            return (new SerializableDocument($object, $metadata))->serialize();
        }

        return $object->dsn();
    }

    final public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Document;
    }

    /**
     * @param string|array $data
     */
    final public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Document
    {
        if (\is_string($data)) {
            $parsedUrl = parse_url($data);
            \assert(isset($parsedUrl['path'], $parsedUrl['scheme']));

            $data = ['path' => $parsedUrl['path'], 'library' => $parsedUrl['scheme']];
        }

        if ($context[self::RENAME] ?? false) {
            unset($data['path']);
        }

        $document = new LazyDocument($data);
        $document->setLibrary($this->registry());

        if (!isset($data['path'])) {
            $document->setNamer($this->namer(), $context);
        }

        return $document;
    }

    final public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Document::class === $type;
    }

    final public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    protected function registry(): LibraryRegistry
    {
        return $this->registry;
    }

    protected function namer(): Namer
    {
        return $this->namer;
    }
}
