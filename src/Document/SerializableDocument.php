<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SerializableDocument implements Document
{
    public function __construct(private Document $document, private array $fields)
    {
    }

    public function serialize(): array
    {
        $data = [];

        foreach ($this->fields as $field) {
            if (!\method_exists($this->document, $field)) {
                throw new \LogicException(); // todo
            }

            $data[$field] = $this->document->{$field}();
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

    public function checksum(array $config = []): string
    {
        return $this->document->checksum();
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
        $clone = clone $this;
        $clone->document = $this->document->refresh();

        return $clone;
    }
}