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
            $parsedUrl = \parse_url($metadata);
            $metadata = [];
            if (isset($parsedUrl['path'])) {
                $metadata['path'] = $parsedUrl['path'];
            } else {
                throw new \LogicException('Path is required to construct lazy document from string.');
            }

            if (isset($parsedUrl['scheme'])) {
                $metadata['library'] = $parsedUrl['scheme'];
            }
        }

        if (!isset($metadata['library'])) {
            throw new \LogicException('Library metadata is required to construct lazy document.');
        }

        $this->metadata = $metadata;
    }

    public function setLibrary(LibraryRegistry $registry): static
    {
        $this->library = $registry->get($this->metadata['library']);

        return $this;
    }

    public function setNamer(Namer $namer, array $context): static
    {
        $this->namer = $namer;
        $this->namerContext = $context;

        return $this;
    }

    public function dsn(): string
    {
        if (isset($this->metadata[__FUNCTION__])) {
            return $this->metadata[__FUNCTION__];
        }

        if (isset($this->document)) {
            return $this->document->dsn();
        }

        if (isset($this->library)) {
            $libraryId = $this->library->id();
        } elseif (isset($this->metadata['library'])) {
            $libraryId = $this->metadata['library'];
        } else {
            throw new \LogicException('A library object or metadata entry is required to generate the dsn.');
        }

        return \sprintf('%s:%s', $libraryId, $this->path());
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

        $clone = clone $this;
        $clone->metadata[__FUNCTION__] = ''; // prevents infinite recursion

        return $this->metadata[__FUNCTION__] = $this->namer->generateName($clone, $this->namerContext);
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

    public function publicUrl(array $config = []): string
    {
        if ($config) {
            return $this->document()->publicUrl($config);
        }

        return $this->metadata[__FUNCTION__] ??= $this->document()->publicUrl();
    }

    public function temporaryUrl(\DateTimeInterface|string $expires, array $config = []): string
    {
        return $this->document()->temporaryUrl($expires, $config);
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
