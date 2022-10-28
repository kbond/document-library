<?php

namespace Zenstruck\Document\Library\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Zenstruck\Document\Library;
use Zenstruck\Document\Library\FlysystemLibrary;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    protected static Library $library;
    protected static LibraryRegistry $libraryRegistry;

    protected function setUp(): void
    {
        self::$library = self::inMemoryLibrary();
        self::$libraryRegistry = self::libraryRegistry();
    }

    protected static function inMemoryLibrary(array $config = []): Library
    {
        return new FlysystemLibrary(new Filesystem(new InMemoryFilesystemAdapter(), $config));
    }

    protected static function libraryRegistry(array $libraries = []): LibraryRegistry
    {
        return new LibraryRegistry(\array_merge(['memory' => self::inMemoryLibrary()], $libraries));
    }
}
