<?php

namespace Zenstruck\Document\Library\Tests\File;

use Zenstruck\Document;
use Zenstruck\Document\Library\Tests\DocumentTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemFileTest extends DocumentTest
{
    protected function document(string $path, \SplFileInfo $file): Document
    {
        return self::inMemoryLibrary()->store($path, $file);
    }
}
