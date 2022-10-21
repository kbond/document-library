<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence;

use Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping\ManagerRegistryMappingProvider;
use Zenstruck\Document\Bridge\Doctrine\Persistence\ObjectDocumentLoader;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\HasORM;
use Zenstruck\Document\Library\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ObjectDocumentLoaderTest extends TestCase
{
    use HasORM;

    /**
     * @test
     */
    public function can_load_documents_for_entity(): void
    {
        $registry = self::libraryRegistry();
        $loader = new ObjectDocumentLoader($registry, new ManagerRegistryMappingProvider($this->doctrine()));

        $entity = new Entity1();
        $entity->document1 = $registry->get('memory')->store('some/file.txt', 'content');
        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $loader->load($entity);

        $this->assertSame('some/file.txt', $entity->document1->path());
        $this->assertSame('content', $entity->document1->contents()); // loaded from filesystem
    }
}
