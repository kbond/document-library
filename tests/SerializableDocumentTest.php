<?php

namespace Zenstruck\Document\Library\Tests;

use Zenstruck\Document;
use Zenstruck\Document\SerializableDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SerializableDocumentTest extends DocumentTest
{
    /**
     * @test
     */
    public function can_serialize_with_fields(): void
    {
        $library = self::inMemoryLibrary(['public_url' => '/']);
        $document = new SerializableDocument($library->store('the/path.txt', 'content'), [
            'path',
            'name',
            'nameWithoutExtension',
            'extension',
            'lastModified',
            'size',
            'checksum',
            'url',
            'mimeType',
        ]);

        $this->assertSame(
            [
                'path' => 'the/path.txt',
                'name' => 'path.txt',
                'nameWithoutExtension' => 'path',
                'extension' => 'txt',
                'lastModified' => \time(),
                'size' => 7,
                'checksum' => '9a0364b9e99bb480dd25e1f0284c8555',
                'url' => '/the/path.txt',
                'mimeType' => 'text/plain',
            ],
            $document->serialize()
        );
    }

    protected function document(string $path, \SplFileInfo $file): Document
    {
        return new SerializableDocument(self::$library->store($path, $file), []);
    }

    protected function modifyDocument(string $path, string $content): void
    {
        self::$library->store($path, $content);
    }
}
