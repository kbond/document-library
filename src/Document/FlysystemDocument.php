<?php

namespace Zenstruck\Document;

use League\Flysystem\FilesystemOperator;
use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemDocument implements Document
{
    private string $path;
    private int $lastModified;
    private int $size;
    private string $mimeType;

    public function __construct(private FilesystemOperator $filesystem, string $path)
    {
        if ('' === $this->path = \ltrim($path, '/')) {
            throw new \InvalidArgumentException(); // todo
        }
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
        return $this->lastModified ??= $this->filesystem->lastModified($this->path);
    }

    public function size(): int
    {
        return $this->size ??= $this->filesystem->fileSize($this->path);
    }

    public function checksum(array $config = []): string
    {
        if (!\method_exists($this->filesystem, 'checksum')) {
            throw new \LogicException(); // todo
        }

        return $this->filesystem->checksum($this->path, $config);
    }

    public function contents(): string
    {
        return $this->filesystem->read($this->path);
    }

    public function read()
    {
        return $this->filesystem->readStream($this->path);
    }

    public function url(array $config = []): string
    {
        if (!\method_exists($this->filesystem, 'publicUrl')) {
            throw new \LogicException(); // todo
        }

        return $this->filesystem->publicUrl($this->path, $config);
    }

    public function exists(): bool
    {
        return $this->filesystem->fileExists($this->path);
    }

    public function mimeType(): string
    {
        return $this->mimeType ??= $this->filesystem->mimeType($this->path);
    }

    public function refresh(): static
    {
        unset($this->size, $this->lastModified, $this->mimeType);

        return $this;
    }
}
