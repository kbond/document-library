<?php

namespace Zenstruck\Document\Library\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Zenstruck\Document\Library;
use Zenstruck\Document\Library\FlysystemLibrary;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    protected const FIXTURE_DIR = __DIR__.'/Fixture/files';

    protected static Library $library;
    protected static Library $fixtures;
    protected static LibraryRegistry $libraryRegistry;

    protected function setUp(): void
    {
        self::$library = self::inMemoryLibrary();
        self::$fixtures = new FlysystemLibrary(new Filesystem(new ReadOnlyFilesystemAdapter(new LocalFilesystemAdapter(self::FIXTURE_DIR))));
        self::$libraryRegistry = self::libraryRegistry();
    }

    protected static function inMemoryLibrary(array $config = []): Library
    {
        return new FlysystemLibrary(new Filesystem(new InMemoryFilesystemAdapter(), $config));
    }

    protected static function libraryRegistry(array $libraries = []): LibraryRegistry
    {
        return new LibraryRegistry(\array_merge(
            [
                'memory' => self::$library,
                'fixtures' => self::$fixtures,
            ],
            $libraries
        ));
    }
}
