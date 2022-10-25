<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Library\Bridge\Symfony\Serializer\DocumentNormalizer;
use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DocumentNormalizerTest extends TestCase
{
    private static LibraryRegistry $registry;

    /**
     * @test
     */
    public function can_serialize_and_unserialize_document(): void
    {
        $document = self::registry()->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json');

        $this->assertSame(\json_encode('some/file.txt'), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json');

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());

        $this->expectException(\LogicException::class);
        $deserialized->mimeType();
    }

    /**
     * @test
     */
    public function can_serialize_and_unserialize_document_and_set_library(): void
    {
        $document = self::registry()->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json');

        $this->assertSame(\json_encode('some/file.txt'), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json', ['library' => 'memory']);

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());
        $this->assertSame('text/plain', $deserialized->mimeType());
    }

    /**
     * @test
     */
    public function can_serialize_and_unserialize_document_with_metadata(): void
    {
        $document = self::registry()->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json', ['metadata' => ['path', 'mimeType', 'size']]);

        $this->assertSame(\json_encode(['path' => 'some/file.txt', 'mimeType' => 'text/plain', 'size' => 7]), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json');

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());
        $this->assertSame('text/plain', $deserialized->mimeType());
        $this->assertSame(7, $deserialized->size());

        $this->expectException(\LogicException::class);
        $deserialized->lastModified();
    }

    /**
     * @test
     */
    public function can_serialize_and_unserialize_document_with_metadata_and_set_library(): void
    {
        $document = self::registry()->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json', ['metadata' => ['path', 'mimeType', 'size']]);

        $this->assertSame(\json_encode(['path' => 'some/file.txt', 'mimeType' => 'text/plain', 'size' => 7]), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json', ['library' => 'memory']);

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());
        $this->assertSame('text/plain', $deserialized->mimeType());
        $this->assertSame(7, $deserialized->size());
        $this->assertSame('9a0364b9e99bb480dd25e1f0284c8555', $deserialized->checksum());
    }

    protected static function normalizer(): DocumentNormalizer
    {
        return new DocumentNormalizer(self::registry());
    }

    protected static function registry(): LibraryRegistry
    {
        return self::$registry ??= self::libraryRegistry();
    }

    private static function serializer(): Serializer
    {
        return new Serializer([new DocumentNormalizer(self::registry())], [new JsonEncoder()]);
    }
}
