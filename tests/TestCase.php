<?php

namespace Zenstruck\Document\Library\Tests;

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
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

    public static function inMemoryLibrary(array $config = []): Library
    {
        return new FlysystemLibrary('memory', new Filesystem(
            new InMemoryFilesystemAdapter(),
            \array_merge(['public_url' => '/'], $config),
            temporaryUrlGenerator: new class() implements TemporaryUrlGenerator {
                public function temporaryUrl(string $path, \DateTimeInterface $expiresAt, Config $config): string
                {
                    return '/'.$path.'?expires';
                }
            }
        ));
    }

    public static function libraryRegistry(array $libraries = []): LibraryRegistry
    {
        return new LibraryRegistry(\array_merge(['memory' => self::$library], $libraries));
    }

    protected static function tempFile(string|\SplFileInfo $content, ?string $extension = null): \SplFileInfo
    {
        $filename = \tempnam(\sys_get_temp_dir(), 'zsdl_');

        if ($extension) {
            \rename($filename, $filename = "{$filename}.{$extension}");
        }

        if (\is_string($content)) {
            \file_put_contents($filename, $content);
        } else {
            \copy($content, $filename);
        }

        \clearstatcache(false, $filename);

        \register_shutdown_function(function() use ($filename) {
            if (\file_exists($filename)) {
                \unlink($filename);
            }
        });

        return new \SplFileInfo($filename);
    }
}
