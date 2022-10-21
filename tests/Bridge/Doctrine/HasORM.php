<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Document\Bridge\Doctrine\DBAL\Types\DocumentType;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait HasORM
{
    private static ?EntityManagerInterface $em = null;

    protected function doctrine(): ManagerRegistry
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->method('getManagerForClass')->withAnyParameters()->willReturn($this->em());

        return $doctrine;
    }

    protected function em(): EntityManagerInterface
    {
        if (isset(self::$em)) {
            return self::$em;
        }

        if (!Type::hasType(DocumentType::NAME)) {
            Type::addType(DocumentType::NAME, DocumentType::class);
        }

        self::$em = EntityManager::create(
            ['driver' => 'pdo_sqlite', 'memory' => true],
            ORMSetup::createAttributeMetadataConfiguration([], true)
        );

        $schemaTool = new SchemaTool(self::$em);
        $schemaTool->createSchema([
            self::$em->getClassMetadata(Entity1::class),
        ]);

        return self::$em;
    }

    /**
     * @after
     */
    protected function teardownEntityManager(): void
    {
        if (!isset(self::$em)) {
            return;
        }

        self::$em->close();
        self::$em = null;
    }
}
