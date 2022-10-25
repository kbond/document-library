<?php

namespace Zenstruck\Document\Library\Tests;

use Zenstruck\Document;
use Zenstruck\Document\Library;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemDocumentTest extends DocumentTest
{
    private Library $library;

    protected function setUp(): void
    {
        $this->library = self::inMemoryLibrary();
    }

    protected function document(string $path, \SplFileInfo $file): Document
    {
        return $this->library->store($path, $file);
    }

    protected function modifyDocument(string $path, string $content): void
    {
        $this->library->store($path, $content);
    }
}
