<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Library
{
    public function open(string $path): Document;

    public function has(string $path): bool;

    public function store(string $path, Document|\SplFileInfo|string $document, array $config = []): Document;

    public function delete(string $path): static;
}
