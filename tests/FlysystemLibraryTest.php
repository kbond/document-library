<?php

namespace Zenstruck\Document\Library\Tests;

use Zenstruck\Document\Library;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemLibraryTest extends LibraryTest
{
    protected function library(): Library
    {
        return self::inMemoryLibrary();
    }
}
