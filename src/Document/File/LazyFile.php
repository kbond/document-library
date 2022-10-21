<?php

namespace Zenstruck\Document\File;

use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Library;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyFile implements LazyDocument
{
    private array $metadata;
    private Document $document;

    public function __construct(string|array $metadata, private ?Library $library = null)
    {
        if (\is_string($metadata)) {
            $metadata = ['path' => $metadata];
        }

        $this->metadata = $metadata;
    }

    public function setLibrary(Library $library): static
    {
        $this->library = $library;

        return $this;
    }

    public function path(): string
    {
        return $this->metadata[__FUNCTION__] ?? throw new \LogicException(); // todo use namer
    }

    public function name(): string
    {
        return $this->metadata[__FUNCTION__] ??= \pathinfo($this->path(), \PATHINFO_BASENAME);
    }

    public function nameWithoutExtension(): string
    {
        return $this->metadata[__FUNCTION__] ??= \pathinfo($this->path(), \PATHINFO_FILENAME);
    }

    public function extension(): string
    {
        return $this->metadata[__FUNCTION__] ??= \pathinfo($this->path(), \PATHINFO_EXTENSION);
    }

    public function lastModified(): int
    {
        return $this->metadata[__FUNCTION__] ??= $this->document()->lastModified();
    }

    public function size(): int
    {
        return $this->metadata[__FUNCTION__] ??= $this->document()->size();
    }

    public function checksum(array $config = []): string
    {
        if ($config) {
            return $this->document()->checksum($config);
        }

        return $this->metadata[__FUNCTION__] ??= $this->document()->checksum();
    }

    public function contents(): string
    {
        return $this->document()->contents();
    }

    public function read()
    {
        return $this->document()->read();
    }

    public function url(array $config = []): string
    {
        if ($config) {
            return $this->document()->url($config);
        }

        return $this->metadata[__FUNCTION__] ??= $this->document()->url();
    }

    public function exists(): bool
    {
        return $this->document()->exists();
    }

    public function mimeType(): string
    {
        return $this->metadata[__FUNCTION__] ??= $this->document()->mimeType();
    }

    public function refresh(): static
    {
        $clone = clone $this;
        $clone->document = $this->document()->refresh();
        $clone->metadata = [];

        return $clone;
    }

    private function document(): Document
    {
        $this->library ?? throw new \LogicException(); // todo

        return $this->document ??= $this->library->open($this->path());
    }
}
