<?php

namespace Zenstruck\Document\Library;

use League\Flysystem\FilesystemOperator;
use Zenstruck\Document;
use Zenstruck\Document\FlysystemDocument;
use Zenstruck\Document\Library;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemLibrary implements Library
{
    public function __construct(
        private string $id,
        private FilesystemOperator $filesystem
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function open(string $path): Document
    {
        return new FlysystemDocument($this->id, $this->filesystem, $path);
    }

    public function has(string $path): bool
    {
        return $this->filesystem->fileExists($path);
    }

    public function store(string $path, Document|\SplFileInfo|string $document, array $config = []): Document
    {
        if (\is_string($document)) {
            $this->filesystem->write($path, $document, $config);

            return $this->open($path);
        }

        if (false === $stream = $document instanceof Document ? $document->read() : \fopen($document, 'r')) {
            throw new \RuntimeException(\sprintf('Unable to read "%s".', $document instanceof Document ? $document->path() : $document));
        }

        $this->filesystem->writeStream($path, $stream, $config);

        \fclose($stream);

        return $this->open($path);
    }

    public function delete(string $path): static
    {
        $this->filesystem->delete($path);

        return $this;
    }
}
