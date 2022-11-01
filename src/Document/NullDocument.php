<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class NullDocument implements Document
{
    public function path(): string
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function name(): string
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function nameWithoutExtension(): string
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function extension(): string
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function lastModified(): int
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function size(): int
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function checksum(array|string $config = []): string
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function contents(): string
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function read()
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function publicUrl(array $config = []): string
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function exists(): bool
    {
        return false;
    }

    public function mimeType(): string
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function refresh(): static
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }

    public function tempFile(): \SplFileInfo
    {
        throw new \BadMethodCallException(\sprintf('%s() is not available.', __METHOD__));
    }
}
