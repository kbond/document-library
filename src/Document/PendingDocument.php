<?php

namespace Zenstruck\Document;

use League\Flysystem\Local\FallbackMimeTypeDetector;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Document;

/**
 * Represents a local file that is not yet added to a {@see Library}.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PendingDocument implements Document
{
    private \SplFileInfo $file;
    private string $path;

    public function __construct(\SplFileInfo|string $file)
    {
        $this->file = \is_string($file) ? new \SplFileInfo($file) : $file;
    }

    /**
     * @immutable
     */
    public function withPath(string $path): self
    {
        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    public function path(): string
    {
        return $this->path ?? (string) $this->file;
    }

    public function name(): string
    {
        return $this->file instanceof UploadedFile ? $this->file->getClientOriginalName() : $this->file->getFilename();
    }

    public function nameWithoutExtension(): string
    {
        return \pathinfo($this->name(), \PATHINFO_FILENAME);
    }

    public function extension(): string
    {
        return $this->file instanceof UploadedFile ? $this->file->getClientOriginalExtension() : $this->file->getExtension();
    }

    public function lastModified(): int
    {
        return $this->file->getMTime();
    }

    public function size(): int
    {
        return $this->file->getSize();
    }

    public function checksum(array $config = []): string
    {
        // todo support other algorithms
        return \md5_file($this->file) ?: throw new \RuntimeException(); // todo
    }

    public function contents(): string
    {
        return \file_get_contents($this->file) ?: throw new \RuntimeException(); // todo
    }

    public function read()
    {
        return \fopen($this->file, 'r') ?: throw new \RuntimeException(); // todo
    }

    public function url(array $config = []): string
    {
        throw new \BadMethodCallException(); // todo
    }

    public function exists(): bool
    {
        return $this->file->isFile();
    }

    public function mimeType(): string
    {
        if ($this->file instanceof UploadedFile) {
            return $this->file->getClientMimeType();
        }

        // todo add as static property?
        return (new FallbackMimeTypeDetector(new FinfoMimeTypeDetector()))->detectMimeTypeFromFile($this->file)
            ?? throw new \RuntimeException()
        ;
    }

    public function refresh(): static
    {
        \clearstatcache(false, $this->file);

        return $this;
    }

    public function tempFile(): \SplFileInfo
    {
        return TempFile::for($this);
    }
}
