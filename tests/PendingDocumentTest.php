<?php

namespace Zenstruck\Document\Library\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Document;
use Zenstruck\Document\PendingDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PendingDocumentTest extends DocumentTest
{
    /**
     * @test
     */
    public function access_document_path_info(): void
    {
        $document = new PendingDocument(new \SplFileInfo(__FILE__));

        $this->assertSame(__FILE__, $document->path());
        $this->assertSame(\pathinfo(__FILE__, \PATHINFO_BASENAME), $document->name());
        $this->assertSame('php', $document->extension());
        $this->assertSame(\pathinfo(__FILE__, \PATHINFO_FILENAME), $document->nameWithoutExtension());
    }

    /**
     * @test
     */
    public function can_create_for_uploaded_file(): void
    {
        $document = new PendingDocument(new UploadedFile(__FILE__, 'file.txt', 'text/plain'));

        $this->assertSame(__FILE__, $document->path());
        $this->assertSame('file.txt', $document->name());
        $this->assertSame('txt', $document->extension());
        $this->assertSame('file', $document->nameWithoutExtension());
        $this->assertSame('text/plain', $document->mimeType());
        $this->assertSame(\filemtime(__FILE__), $document->lastModified());
        $this->assertSame(\filesize(__FILE__), $document->size());
        $this->assertSame(\md5_file(__FILE__), $document->checksum());
        $this->assertStringEqualsFile(__FILE__, $document->contents());
        $this->assertStringEqualsFile(__FILE__, \stream_get_contents($document->read()));
        $this->assertTrue($document->exists());
    }

    protected function document(string $path, \SplFileInfo $file): Document
    {
        return new PendingDocument($file);
    }
}
