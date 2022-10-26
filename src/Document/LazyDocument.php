<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class LazyDocument implements Document
{
    private array $metadata;
    private Library $library;
    private Document $document;
    private Namer $namer;
    private array $namerContext;

    public function __construct(string|array $metadata)
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

    public function isNamerRequired(): bool
    {
        return !isset($this->document) && !isset($this->metadata['path']);
    }

    public function setNamer(Namer $namer, array $context): static
    {
        $this->namer = $namer;
        $this->namerContext = $context;

        return $this;
    }

    public function path(): string
    {
        if (isset($this->metadata[__FUNCTION__])) {
            return $this->metadata[__FUNCTION__];
        }

        if (isset($this->document)) {
            return $this->document->path();
        }

        if (!isset($this->namer)) {
            throw new \LogicException('A namer is required to generate the path from metadata.');
        }

        return $this->metadata[__FUNCTION__] = $this->namer->generateName($this, $this->namerContext);
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

    public function checksum(array|string $config = []): string
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
        $this->document()->refresh();
        $this->metadata = [];

        return $this;
    }

    public function tempFile(): \SplFileInfo
    {
        return $this->document()->tempFile();
    }

    private function document(): Document
    {
        $this->library ?? throw new \LogicException('A library has not been set for this document.');

        try {
            return $this->document ??= $this->library->open($this->path());
        } finally {
            $this->metadata = [];
        }
    }
}
