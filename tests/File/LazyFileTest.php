<?php

namespace Zenstruck\Document\Library\Tests\File;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Zenstruck\Document;
use Zenstruck\Document\File\LazyFile;
use Zenstruck\Document\Library\FlysystemLibrary;
use Zenstruck\Document\Library\Tests\DocumentTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyFileTest extends DocumentTest
{
    protected function document(string $path, \SplFileInfo $file): Document
    {
        $library = new FlysystemLibrary(new Filesystem(new InMemoryFilesystemAdapter()));
        $library->store($path, $file);

        return (new LazyFile($path))->setLibrary($library);
    }
}
