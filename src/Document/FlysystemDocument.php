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

    /** @var array<string,string> */
    private array $checksum = [];

    /** @var array<string,string> */
    private array $publicUrl = [];

    /** @var array<string,string> */
    private array $temporaryUrl = [];

    public function __construct(private string $libraryId, private FilesystemOperator $filesystem, string $path)
    {
        if ('' === $this->path = \ltrim($path, '/')) {
            throw new \InvalidArgumentException('Path cannot be empty.');
        }
    }

    public function dsn(): string
    {
        return \sprintf('%s:%s', $this->libraryId, $this->path);
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

    public function checksum(array|string $config = []): string
    {
        if (\is_string($config)) {
            $config = ['checksum_algo' => $config];
        }

        if (isset($this->checksum[$serialized = \serialize($config)])) {
            return $this->checksum[$serialized];
        }

        if (!\method_exists($this->filesystem, 'checksum')) {
            throw new \LogicException('Checksum is not available for this filesystem.');
        }

        return $this->checksum[$serialized] = $this->filesystem->checksum($this->path, $config);
    }

    public function contents(): string
    {
        return $this->filesystem->read($this->path);
    }

    public function read()
    {
        return $this->filesystem->readStream($this->path);
    }

    public function publicUrl(array $config = []): string
    {
        if (isset($this->publicUrl[$serialized = \serialize($config)])) {
            return $this->publicUrl[$serialized];
        }

        if (!\method_exists($this->filesystem, 'publicUrl')) {
            throw new \LogicException('A publicUrl is not available for this filesystem.');
        }

        return $this->publicUrl[$serialized] = $this->filesystem->publicUrl($this->path, $config);
    }

    public function temporaryUrl(\DateTimeInterface|string $expires, array $config = []): string
    {
        if (\is_string($expires)) {
            $expires = new \DateTimeImmutable($expires);
        }

        if (isset($this->temporaryUrl[$serialized = \serialize([$expires, $config])])) {
            return $this->temporaryUrl($serialized);
        }

        if (!\method_exists($this->filesystem, 'temporaryUrl')) {
            throw new \LogicException('A temporaryUrl is not available for this filesystem.');
        }

        return $this->temporaryUrl[$serialized] = $this->filesystem->temporaryUrl($this->path, $expires, $config);
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
        $this->checksum = $this->publicUrl = $this->temporaryUrl = [];

        return $this;
    }
}
