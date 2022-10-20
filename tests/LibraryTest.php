<?php

namespace Zenstruck\Document\Library\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;
use Zenstruck\Document\Library;
use Zenstruck\Document\Library\FlysystemLibrary;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class LibraryTest extends TestCase
{
    /**
     * @test
     * @dataProvider storeProvider
     */
    public function can_store($what): void
    {
        $filesystem = $this->library();

        $this->assertFalse($filesystem->has('some/file.txt'));

        $document = $filesystem->store('some/file.txt', $what);

        $this->assertTrue($filesystem->has('some/file.txt'));
        $this->assertSame('some/file.txt', $document->path());
        $this->assertSame('file.txt', $document->name());
        $this->assertSame('txt', $document->extension());
        $this->assertSame('file', $document->nameWithoutExtension());
        $this->assertSame(\time(), $document->lastModified());
        $this->assertSame(\filesize(__FILE__), $document->size());
        $this->assertSame(\md5_file(__FILE__), $document->checksum());
        $this->assertStringEqualsFile(__FILE__, $document->contents());
        $this->assertStringEqualsFile(__FILE__, \stream_get_contents($document->read()));
        $this->assertTrue($document->exists());
        $this->assertSame('text/x-php', $document->mimeType());
    }

    public static function storeProvider(): iterable
    {
        yield [\file_get_contents(__FILE__)];
        yield [new \SplFileInfo(__FILE__)];
        yield [(new FlysystemLibrary(new Filesystem(new InMemoryFilesystemAdapter())))->store('temp.file', new \SplFileInfo(__FILE__))];
    }

    /**
     * @test
     */
    public function can_delete(): void
    {
        $library = $this->library();

        $library->store('file.txt', 'content');

        $this->assertTrue($library->has('file.txt'));

        $library->delete('file.txt');

        $this->assertFalse($library->has('file.txt'));
    }

    abstract protected function library(): Library;
}
