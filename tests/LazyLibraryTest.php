<?php

namespace Zenstruck\Document\Library\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Zenstruck\Document\Library;
use Zenstruck\Document\Library\FlysystemLibrary;
use Zenstruck\Document\Library\LazyLibrary;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyLibraryTest extends LibraryTest
{
    protected function library(): Library
    {
        return new LazyLibrary(fn() => new FlysystemLibrary(new Filesystem(new InMemoryFilesystemAdapter())));
    }
}
