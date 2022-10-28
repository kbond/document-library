<?php

namespace Zenstruck\Document\Library\Tests;

use Zenstruck\Document\Library;
use Zenstruck\Document\Library\LazyLibrary;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyLibraryTest extends LibraryTest
{
    protected function library(): Library
    {
        return new LazyLibrary(fn() => self::$library);
    }
}
