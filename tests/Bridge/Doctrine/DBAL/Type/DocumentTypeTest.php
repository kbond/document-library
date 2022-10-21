<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\DBAL\Type;

use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\HasORM;
use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\SerializableDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentTypeTest extends TestCase
{
    use HasORM;

    /**
     * @test
     */
    public function can_persist_null_document(): void
    {
        $entity = new Entity1();

        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertNull($entity->document1);
    }

    /**
     * @test
     */
    public function can_persist_update_and_remove_document(): void
    {
        $entity = new Entity1();
        $entity->document1 = self::inMemoryLibrary()->store('some/file.txt', 'content');

        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertInstanceOf(LazyDocument::class, $entity->document1);
        $this->assertSame('some/file.txt', $entity->document1->path());

        $entity->document1 = self::inMemoryLibrary()->store('another/file.txt', 'content');
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertInstanceOf(LazyDocument::class, $entity->document1);
        $this->assertSame('another/file.txt', $entity->document1->path());

        $entity->document1 = null;
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertNull($entity->document1);
    }

    /**
     * @test
     */
    public function can_persist_update_and_remove_document_with_metadata(): void
    {
        $entity = new Entity1();
        $entity->document1 = new SerializableDocument(self::inMemoryLibrary()->store('some/file.txt', 'content'), [
            'path', 'mimeType', 'size',
        ]);

        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertInstanceOf(LazyDocument::class, $entity->document1);
        $this->assertSame('some/file.txt', $entity->document1->path());
        $this->assertSame('text/plain', $entity->document1->mimeType());
        $this->assertSame(7, $entity->document1->size());

        $entity->document1 = new SerializableDocument(self::inMemoryLibrary()->store('another/file.txt', 'new content'), [
            'path', 'mimeType', 'size',
        ]);
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertInstanceOf(LazyDocument::class, $entity->document1);
        $this->assertSame('another/file.txt', $entity->document1->path());
        $this->assertSame('text/plain', $entity->document1->mimeType());
        $this->assertSame(11, $entity->document1->size());

        $entity->document1 = null;
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertNull($entity->document1);
    }
}
