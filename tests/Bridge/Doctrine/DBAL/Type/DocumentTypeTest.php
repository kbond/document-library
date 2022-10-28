<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\DBAL\Type;

use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\HasORM;
use Zenstruck\Document\Library\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class DocumentTypeTest extends TestCase
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

        $this->assertNull($entity->{$this->documentProperty()});
    }

    /**
     * @test
     */
    public function can_persist_update_and_remove_document(): void
    {
        $entity = new Entity1();
        $entity->{$this->documentProperty()} = self::$library->store('some/file.txt', 'content');

        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertInstanceOf(LazyDocument::class, $entity->{$this->documentProperty()});
        $this->assertSame('some/file.txt', $entity->{$this->documentProperty()}->path());

        $entity->{$this->documentProperty()} = self::$library->store('another/file.txt', 'content');
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertInstanceOf(LazyDocument::class, $entity->{$this->documentProperty()});
        $this->assertSame('another/file.txt', $entity->{$this->documentProperty()}->path());

        $entity->{$this->documentProperty()} = null;
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertNull($entity->{$this->documentProperty()});
    }

    abstract protected function documentProperty(): string;
}
