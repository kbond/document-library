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
    private Document $document;

    public function __construct(private string $path, private ?Library $library = null)
    {
    }

    public function setLibrary(Library $library): static
    {
        $this->library = $library;

        return $this;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function name(): string
    {
        return \pathinfo($this->path, \PATHINFO_BASENAME);
    }

    public function nameWithoutExtension(): string
    {
        return \pathinfo($this->path, \PATHINFO_FILENAME);
    }

    public function extension(): string
    {
        return \pathinfo($this->path, \PATHINFO_EXTENSION);
    }

    public function lastModified(): int
    {
        return $this->document()->lastModified();
    }

    public function size(): int
    {
        return $this->document()->size();
    }

    public function checksum(array $config = []): string
    {
        return $this->document()->checksum($config);
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
        return $this->document()->url($config);
    }

    public function exists(): bool
    {
        return $this->document()->exists();
    }

    public function mimeType(): string
    {
        return $this->document()->mimeType();
    }

    public function refresh(): static
    {
        $this->document()->refresh();

        return $this;
    }

    private function document(): Document
    {
        $this->library ?? throw new \LogicException(); // todo

        return $this->document ??= $this->library->open($this->path);
    }
}
