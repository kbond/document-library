<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Library\Bridge\Symfony\Serializer\DocumentNormalizer;
use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\Namer\MultiNamer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DocumentNormalizerTest extends TestCase
{
    /**
     * @test
     */
    public function can_serialize_and_deserialize_document(): void
    {
        $document = self::$libraryRegistry->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json');

        $this->assertSame(\json_encode('memory:some/file.txt'), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json');

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('memory:some/file.txt', $deserialized->dsn());
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());
    }

    /**
     * @test
     */
    public function can_serialize_and_deserialize_document_and_set_library(): void
    {
        $document = self::$libraryRegistry->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json');

        $this->assertSame(\json_encode('memory:some/file.txt'), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json', ['library' => 'memory']);

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('memory:some/file.txt', $deserialized->dsn());
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());
        $this->assertSame('text/plain', $deserialized->mimeType());
    }

    /**
     * @test
     */
    public function can_serialize_and_deserialize_document_as_path(): void
    {
        $document = self::$libraryRegistry->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json', ['only_path' => true]);

        $this->assertSame(\json_encode('some/file.txt'), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json', ['library' => 'memory']);

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('memory:some/file.txt', $deserialized->dsn());
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());
    }

    /**
     * @test
     */
    public function can_serialize_and_deserialize_document_with_metadata(): void
    {
        $document = self::$libraryRegistry->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json', ['metadata' => ['library', 'path', 'mimeType', 'size']]);

        $this->assertSame(\json_encode(['library' => 'memory', 'path' => 'some/file.txt', 'mimeType' => 'text/plain', 'size' => 7]), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json');

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('memory:some/file.txt', $deserialized->dsn());
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());
        $this->assertSame('text/plain', $deserialized->mimeType());
        $this->assertSame(7, $deserialized->size());
    }

    /**
     * @test
     */
    public function can_serialize_and_deserialize_document_with_metadata_and_set_library(): void
    {
        $document = self::$libraryRegistry->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json', ['metadata' => ['path', 'mimeType', 'size']]);

        $this->assertSame(\json_encode(['path' => 'some/file.txt', 'mimeType' => 'text/plain', 'size' => 7]), $serialized);

        $deserialized = $serializer->deserialize($serialized, Document::class, 'json', ['library' => 'memory']);

        $this->assertInstanceOf(LazyDocument::class, $deserialized);
        $this->assertSame('memory:some/file.txt', $deserialized->dsn());
        $this->assertSame('some/file.txt', $deserialized->path());
        $this->assertSame('file.txt', $deserialized->name());
        $this->assertSame('file', $deserialized->nameWithoutExtension());
        $this->assertSame('txt', $deserialized->extension());
        $this->assertSame('text/plain', $deserialized->mimeType());
        $this->assertSame(7, $deserialized->size());
        $this->assertSame('9a0364b9e99bb480dd25e1f0284c8555', $deserialized->checksum());
    }

    /**
     * @test
     */
    public function can_serialize_all_metadata(): void
    {
        $document = self::$libraryRegistry->get('memory')->store('some/file.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json', ['metadata' => true]);
        $decoded = \json_decode($serialized, true);

        $this->assertArrayHasKey('lastModified', $decoded);
        $this->assertIsInt($decoded['lastModified']);

        unset($decoded['lastModified']);

        $this->assertSame(
            [
                'library' => 'memory',
                'path' => 'some/file.txt',
                'size' => 7,
                'checksum' => '9a0364b9e99bb480dd25e1f0284c8555',
                'mimeType' => 'text/plain',
                'publicUrl' => '/some/file.txt',
            ],
            $decoded
        );
    }

    /**
     * @test
     */
    public function can_serialize_metadata_without_path_and_names_during_deserialize(): void
    {
        $document = self::$libraryRegistry->get('memory')->store($expected = '9a0364b9e99bb480dd25e1f0284c8555.txt', 'content');
        $serializer = self::serializer();

        $serialized = $serializer->serialize($document, 'json', ['metadata' => ['checksum', 'extension']]);

        $this->assertSame(\json_encode(['checksum' => '9a0364b9e99bb480dd25e1f0284c8555', 'extension' => 'txt']), $serialized);

        $document = $serializer->deserialize($serialized, Document::class, 'json', ['library' => 'memory', 'namer' => 'checksum']);

        $this->assertSame('9a0364b9e99bb480dd25e1f0284c8555', $document->checksum());
        $this->assertSame($expected, $document->path());
        $this->assertSame('text/plain', $document->mimeType());
    }

    protected static function normalizer(): DocumentNormalizer
    {
        return new DocumentNormalizer(self::$libraryRegistry, new MultiNamer());
    }

    private static function serializer(): Serializer
    {
        return new Serializer([self::normalizer()], [new JsonEncoder()]);
    }
}
