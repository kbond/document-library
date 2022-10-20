<?php

namespace Zenstruck\Document\Library\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Zenstruck\Document\Library;
use Zenstruck\Document\Library\FlysystemLibrary;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemLibraryTest extends LibraryTest
{
    protected function library(): Library
    {
        return new FlysystemLibrary(new Filesystem(new InMemoryFilesystemAdapter()));
    }
}
