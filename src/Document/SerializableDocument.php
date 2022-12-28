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
    private const ALL_METADATA_FIELDS = ['library', 'path', 'lastModified', 'size', 'checksum', 'mimeType', 'publicUrl'];

    private array $fields = [];

    public function __construct(
        private Document $document,
        array|bool $fields,
        private readonly SerializationMode $mode = SerializationMode::AsArray
    ) {
        if (SerializationMode::AsArray === $this->mode && false === $fields) {
            throw new \InvalidArgumentException('$fields cannot be false.');
        }

        if (true === $fields) {
            $this->fields = self::ALL_METADATA_FIELDS;
        } else if (is_array($fields)) {
            $this->fields = $fields;
        }
    }

    public function serialize(): array|string
    {
        return match ($this->mode) {
            SerializationMode::AsArray => $this->toArray(),
            SerializationMode::AsDsnString => $this->document->dsn(),
            SerializationMode::AsPathString => $this->document->path(),
        };
    }

    public function dsn(): string
    {
        return $this->document->dsn();
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

    public function publicUrl(array $config = []): string
    {
        return $this->document->publicUrl($config);
    }

    public function temporaryUrl(\DateTimeInterface|string $expires, array $config = []): string
    {
        return $this->document->temporaryUrl($expires, $config);
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

    private function toArray(): array
    {
        $data = [];

        foreach ($this->fields as $field) {
            if ('library' === $field) {
                $parsedUrl = \parse_url($this->document->dsn());

                if (isset($parsedUrl['scheme'])) {
                    $data[$field] = $parsedUrl['scheme'];
                }

                continue;
            }

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
}
