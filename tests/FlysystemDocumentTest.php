<?php

namespace Zenstruck\Document\Library\Tests;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemDocumentTest extends DocumentTest
{
    protected function document(string $path, \SplFileInfo $file): Document
    {
        return self::$library->store($path, $file);
    }

    protected function modifyDocument(string $path, string $content): void
    {
        self::$library->store($path, $content);
    }
}
