<?php

namespace Zenstruck\Document;

use League\Flysystem\FilesystemOperator;
use Zenstruck\Document;
use Zenstruck\Document\Image\FlysystemImage;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
class FlysystemDocument implements Document
{
    private const IMAGE_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'png', 'svg', 'apng', 'avif', 'jfif', 'pjpeg', 'pjp', 'webp'];

    private string $path;
    private int $lastModified;
    private int $size;
    private string $mimeType;

    /** @var array<string,string> */
    private array $checksum = [];

    /** @var array<string,string> */
    private array $url = [];

    final public function __construct(private FilesystemOperator $filesystem, string $path)
    {
        if ('' === $this->path = \ltrim($path, '/')) {
            throw new \InvalidArgumentException('Path cannot be empty.');
        }
    }

    final public function path(): string
    {
        return $this->path;
    }

    final public function name(): string
    {
        return \pathinfo($this->path, \PATHINFO_BASENAME);
    }

    final public function nameWithoutExtension(): string
    {
        return \pathinfo($this->path, \PATHINFO_FILENAME);
    }

    final public function extension(): string
    {
        return \pathinfo($this->path, \PATHINFO_EXTENSION);
    }

    final public function lastModified(): int
    {
        return $this->lastModified ??= $this->filesystem->lastModified($this->path);
    }

    final public function size(): int
    {
        return $this->size ??= $this->filesystem->fileSize($this->path);
    }

    final public function checksum(array|string $config = []): string
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

    final public function contents(): string
    {
        return $this->filesystem->read($this->path);
    }

    final public function read()
    {
        return $this->filesystem->readStream($this->path);
    }

    final public function url(array $config = []): string
    {
        if (isset($this->url[$serialized = \serialize($config)])) {
            return $this->url[$serialized];
        }

        if (!\method_exists($this->filesystem, 'publicUrl')) {
            throw new \LogicException('A publicUrl is not available for this filesystem.');
        }

        return $this->url[$serialized] = $this->filesystem->publicUrl($this->path, $config);
    }

    final public function exists(): bool
    {
        return $this->filesystem->fileExists($this->path);
    }

    final public function mimeType(): string
    {
        return $this->mimeType ??= $this->filesystem->mimeType($this->path);
    }

    public function refresh(): static
    {
        unset($this->size, $this->lastModified, $this->mimeType);
        $this->checksum = $this->url = [];

        return $this;
    }

    final public function tempFile(): \SplFileInfo
    {
        return TempFile::for($this);
    }

    public function asImage(): Image
    {
        if (!$this->isImage()) {
            throw new \RuntimeException(\sprintf('"%s" is not an image.', $this->path()));
        }

        $image = new FlysystemImage($this->filesystem, $this->path);

        if (isset($this->size)) {
            $image->size = $this->size; // @phpstan-ignore-line
        }

        if (isset($this->lastModified)) {
            $image->lastModified = $this->lastModified; // @phpstan-ignore-line
        }

        if (isset($this->mimeType)) {
            $image->mimeType = $this->mimeType; // @phpstan-ignore-line
        }

        $image->checksum = $this->checksum; // @phpstan-ignore-line
        $image->url = $this->url; // @phpstan-ignore-line

        return $image;
    }

    private function isImage(): bool
    {
        if (!$ext = $this->extension()) {
            // only check mime-type if no extension
            return \str_starts_with($this->mimeType(), 'image/');
        }

        return \in_array($ext, self::IMAGE_EXTENSIONS, true);
    }
}
