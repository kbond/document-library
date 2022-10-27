<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\DBAL\Type;

use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\SerializableDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentJsonTypeTest extends DocumentTypeTest
{
    /**
     * @test
     */
    public function can_persist_update_and_remove_document_with_metadata(): void
    {
        $entity = new Entity1();
        $entity->{$this->documentProperty()} = new SerializableDocument(self::inMemoryLibrary()->store('some/file.txt', 'content'), [
            'path', 'mimeType', 'size',
        ]);

        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertInstanceOf(LazyDocument::class, $entity->{$this->documentProperty()});
        $this->assertSame('some/file.txt', $entity->{$this->documentProperty()}->path());
        $this->assertSame('text/plain', $entity->{$this->documentProperty()}->mimeType());
        $this->assertSame(7, $entity->{$this->documentProperty()}->size());

        $entity->{$this->documentProperty()} = new SerializableDocument(self::inMemoryLibrary()->store('another/file.txt', 'new content'), [
            'path', 'mimeType', 'size',
        ]);
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertInstanceOf(LazyDocument::class, $entity->{$this->documentProperty()});
        $this->assertSame('another/file.txt', $entity->{$this->documentProperty()}->path());
        $this->assertSame('text/plain', $entity->{$this->documentProperty()}->mimeType());
        $this->assertSame(11, $entity->{$this->documentProperty()}->size());

        $entity->{$this->documentProperty()} = null;
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertNull($entity->{$this->documentProperty()});
    }

    protected function documentProperty(): string
    {
        return 'document1';
    }
}
