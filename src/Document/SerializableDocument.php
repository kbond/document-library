<?php

namespace Zenstruck\Document;

use League\Flysystem\UnableToGeneratePublicUrl;
use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class SerializableDocument implements Document
{
    private const ALL_METADATA_FIELDS = ['path', 'lastModified', 'size', 'checksum', 'mimeType', 'url'];

    private array $fields;

    public function __construct(private Document $document, array|bool $fields)
    {
        if (false === $fields) {
            throw new \InvalidArgumentException('$fields cannot be false.');
        }

        if (true === $fields) {
            $fields = self::ALL_METADATA_FIELDS;
        }

        $this->fields = $fields;
    }

    public function serialize(): array
    {
        $data = [];

        foreach ($this->fields as $field) {
            if (!\method_exists($this->document, $field)) {
                throw new \LogicException(\sprintf('Method %d::%s() does not exist.', static::class, $field));
            }

            try {
                $data[$field] = $this->document->{$field}();
            } catch (UnableToGeneratePublicUrl) {
                // url not available, skip
            }
        }

        return $data;
    }

    public function path(): string
    {
        return $this->document->path();
    }

    public function name(): string
    {
        return $this->document->name();
    }

    public function nameWithoutExtension(): string
    {
        return $this->document->nameWithoutExtension();
    }

    public function extension(): string
    {
        return $this->document->extension();
    }

    public function lastModified(): int
    {
        return $this->document->lastModified();
    }

    public function size(): int
    {
        return $this->document->size();
    }

    public function checksum(array|string $config = []): string
    {
        return $this->document->checksum($config);
    }

    public function contents(): string
    {
        return $this->document->contents();
    }

    public function read()
    {
        return $this->document->read();
    }

    public function url(array $config = []): string
    {
        return $this->document->url($config);
    }

    public function exists(): bool
    {
        return $this->document->exists();
    }

    public function mimeType(): string
    {
        return $this->document->mimeType();
    }

    public function refresh(): static
    {
        $this->document = $this->document->refresh();

        return $this;
    }

    public function tempFile(): \SplFileInfo
    {
        return $this->document->tempFile();
    }

    public function asImage(): Image
    {
        return $this->document->asImage();
    }
}
