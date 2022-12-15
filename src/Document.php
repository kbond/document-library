<?php

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Document
{
    /**
     * Returns standardized document identification, containing library ID and file path.
     *
     * @example If file comes from "public" library with "foo/bar.txt" path, returns "public:foo/bar.txt"
     */
    public function dsn(): string;

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

    public function checksum(array|string $config = []): string;

    public function contents(): string;

    /**
     * @return resource
     */
    public function read();

    public function publicUrl(array $config = []): string;

    public function temporaryUrl(\DateTimeInterface|string $expires, array $config = []): string;

    /**
     * Check if the document still exists.
     */
    public function exists(): bool;

    public function mimeType(): string;

    /**
     * Clear any cached metadata.
     */
    public function refresh(): static;
}
