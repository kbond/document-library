<?php

namespace Zenstruck\Document\Library\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class DocumentTest extends TestCase
{
    /**
     * @test
     */
    public function access_document_path_info(): void
    {
        $document = $this->document('some/file.txt', new \SplFileInfo(__FILE__));

        $this->assertSame('some/file.txt', $document->path());
        $this->assertSame('file.txt', $document->name());
        $this->assertSame('txt', $document->extension());
        $this->assertSame('file', $document->nameWithoutExtension());
    }

    /**
     * @test
     */
    public function access_document_data(): void
    {
        $document = $this->document('some/file.txt', new \SplFileInfo(__FILE__));

        $this->assertIsInt($document->lastModified());
        $this->assertSame(\filesize(__FILE__), $document->size());
        $this->assertSame(\md5_file(__FILE__), $document->checksum());
        $this->assertStringEqualsFile(__FILE__, $document->contents());
        $this->assertStringEqualsFile(__FILE__, \stream_get_contents($document->read()));
        $this->assertTrue($document->exists());
        $this->assertSame('text/x-php', $document->mimeType());
    }

    abstract protected function document(string $path, \SplFileInfo $file): Document;
}