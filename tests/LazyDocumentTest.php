<?php

namespace Zenstruck\Document\Library\Tests;

use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyDocumentTest extends DocumentTest
{
    /**
     * @test
     */
    public function can_create_with_cached_metadata(): void
    {
        $document = new LazyDocument([
            'path' => '1',
            'name' => '2',
            'nameWithoutExtension' => '3',
            'extension' => '4',
            'lastModified' => 5,
            'size' => 6,
            'checksum' => '7',
            'url' => '8',
            'mimeType' => '9',
        ]);

        $this->assertSame('1', $document->path());
        $this->assertSame('2', $document->name());
        $this->assertSame('3', $document->nameWithoutExtension());
        $this->assertSame('4', $document->extension());
        $this->assertSame(5, $document->lastModified());
        $this->assertSame(6, $document->size());
        $this->assertSame('7', $document->checksum());
        $this->assertSame('8', $document->url());
        $this->assertSame('9', $document->mimeType());
    }

    protected function document(string $path, \SplFileInfo $file): Document
    {
        $library = self::inMemoryLibrary();
        $library->store($path, $file);

        return (new LazyDocument($path))->setLibrary($library);
    }
}