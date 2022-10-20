<?php

namespace Zenstruck\Document\Library\Tests\File;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Zenstruck\Document;
use Zenstruck\Document\Library\FlysystemLibrary;
use Zenstruck\Document\Library\Tests\DocumentTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemFileTest extends DocumentTest
{
    protected function document(string $path, \SplFileInfo $file): Document
    {
        return (new FlysystemLibrary(new Filesystem(new InMemoryFilesystemAdapter())))->store($path, $file);
    }
}
