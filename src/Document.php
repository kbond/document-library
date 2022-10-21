<?php

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Document
{
    public function path(): string;

    /**
     * Returns the file name (with extension if applicable).
     *
     * @example If path is "foo/bar/baz.txt", returns "baz.txt"
     * @example If path is "foo/bar/baz", returns "baz"
     */
    public function name(): string;

    /**
     * @example If $path is "foo/bar/baz.txt", returns "baz"
     * @example If $path is "foo/bar/baz", returns "baz"
     */
    public function nameWithoutExtension(): string;

    public function extension(): string;

    public function lastModified(): int;

    public function size(): int;

    public function checksum(array $config = []): string;

    public function contents(): string;

    /**
     * @return resource
     */
    public function read();

    public function url(array $config = []): string;

    /**
     * Check if the document still exists.
     */
    public function exists(): bool;

    public function mimeType(): string;

    /**
     * Return a fresh instance from the library.
     *
     * @immutable
     */
    public function refresh(): static;
}
