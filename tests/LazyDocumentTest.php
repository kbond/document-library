<?php

namespace Zenstruck\Document\Library\Tests;

use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Namer\ExpressionNamer;

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
            'publicUrl' => '8',
            'mimeType' => '9',
            'library' => '0',
        ]);

        $this->assertSame('0:1', $document->dsn());
        $this->assertSame('1', $document->path());
        $this->assertSame('2', $document->name());
        $this->assertSame('3', $document->nameWithoutExtension());
        $this->assertSame('4', $document->extension());
        $this->assertSame(5, $document->lastModified());
        $this->assertSame(6, $document->size());
        $this->assertSame('7', $document->checksum());
        $this->assertSame('8', $document->publicUrl());
        $this->assertSame('9', $document->mimeType());
    }

    /**
     * @test
     */
    public function library_is_required_to_use_lazy_document(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Missing library metadata.');

        $document = (new LazyDocument(['checksum' => 'foo']));
        $document->setLibrary(self::$libraryRegistry);
        $document->dsn();
    }

    /**
     * @test
     */
    public function can_lazily_provide_library(): void
    {
        $document = (new LazyDocument('test/path'));
        $document->setLibrary(self::$libraryRegistry, 'memory');
        self::assertSame('memory:test/path', $document->dsn());
    }

    /**
     * @test
     */
    public function can_lazily_generate_path_with_namer(): void
    {
        $document = (new LazyDocument(['library' => 'memory', 'checksum' => 'foo']))->setNamer(new ExpressionNamer(), [
            'expression' => 'prefix/{checksum}-{bar}.pdf',
            'bar' => 'baz',
        ]);

        $this->assertSame('prefix/foo-baz.pdf', $document->path());
    }

    /**
     * @test
     */
    public function namer_is_required_to_generate_name(): void
    {
        $document = (new LazyDocument(['library' => 'memory', 'checksum' => 'foo']));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('A namer is required to generate the path from metadata.');

        $document->path();
    }

    protected function document(string $path, \SplFileInfo $file): Document
    {
        $document = self::$library->store($path, $file);

        return (new LazyDocument($document->dsn()))->setLibrary(self::$libraryRegistry);
    }

    protected function modifyDocument(string $path, string $content): void
    {
        self::$library->store($path, $content);
    }
}
