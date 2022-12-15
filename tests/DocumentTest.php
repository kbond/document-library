<?php

namespace Zenstruck\Document\Library\Tests;

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

        $this->assertSame('test:some/file.txt', $document->dsn());
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
        $this->assertSame(\sha1_file(__FILE__), $document->checksum('sha1'));
        $this->assertStringEqualsFile(__FILE__, $document->contents());
        $this->assertStringEqualsFile(__FILE__, \stream_get_contents($document->read()));
        $this->assertTrue($document->exists());
        $this->assertSame('text/x-php', $document->mimeType());
    }

    /**
     * @test
     */
    public function access_public_url(): void
    {
        $document = $this->document('some/file.txt', new \SplFileInfo(__FILE__));

        $this->assertSame('/some/file.txt', $document->publicUrl());
    }

    /**
     * @test
     */
    public function access_temporary_url(): void
    {
        $document = $this->document('some/file.txt', new \SplFileInfo(__FILE__));

        $this->assertSame('/some/file.txt?expires', $document->temporaryUrl('+10 minutes'));
    }

    /**
     * @test
     */
    public function refresh_resets_any_cached_metadata(): void
    {
        $document = $this->document('some/file.txt', new \SplFileInfo(__FILE__));

        $this->assertIsInt($document->lastModified());
        $this->assertSame(\filesize(__FILE__), $document->size());
        $this->assertSame(\md5_file(__FILE__), $document->checksum());
        $this->assertTrue($document->exists());
        $this->assertSame('text/x-php', $document->mimeType());

        $this->modifyDocument('some/file.txt', 'new content');

        $this->assertIsInt($document->lastModified());
        $this->assertSame(\filesize(__FILE__), $document->size());
        $this->assertSame(\md5_file(__FILE__), $document->checksum());
        $this->assertTrue($document->exists());
        $this->assertSame('text/x-php', $document->mimeType());

        $document->refresh();

        $this->assertIsInt($document->lastModified());
        $this->assertSame(11, $document->size());
        $this->assertSame('96c15c2bb2921193bf290df8cd85e2ba', $document->checksum());
        $this->assertTrue($document->exists());
        $this->assertSame('text/plain', $document->mimeType());
    }

    /**
     * @test
     */
    public function refresh_is_mutable(): void
    {
        $document = $this->document('some/file.txt', new \SplFileInfo(__FILE__));

        $this->assertSame($document, $document->refresh());
    }

    abstract protected function document(string $path, \SplFileInfo $file): Document;

    abstract protected function modifyDocument(string $path, string $content): void;
}
