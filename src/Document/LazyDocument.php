<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyDocument implements Document
{
    /** @var callable():Library|Library|null */
    private $library;
    private Document $document;

    /**
     * @param callable():Library|Library|null $library
     */
    public function __construct(private string $path, callable|Library|null $library = null)
    {
        $this->library = $library;
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
        return $this->document ??= $this->library()->open($this->path);
    }

    private function library(): Library
    {
        if ($this->library instanceof Library) {
            return $this->library;
        }

        if (\is_callable($this->library)) {
            return $this->library = ($this->library)();
        }

        throw new \LogicException(); // todo
    }
}
