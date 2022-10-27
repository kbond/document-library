<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types\DocumentJsonType;
use Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types\DocumentStringType;

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
        $doctrine->method('getManagers')->willReturn([$this->em()]);

        return $doctrine;
    }

    protected function em(): EntityManagerInterface
    {
        if (isset(self::$em)) {
            return self::$em;
        }

        if (!Type::hasType(DocumentJsonType::NAME)) {
            Type::addType(DocumentJsonType::NAME, DocumentJsonType::class);
        }

        if (!Type::hasType(DocumentStringType::NAME)) {
            Type::addType(DocumentStringType::NAME, DocumentStringType::class);
        }

        self::$em = EntityManager::create(
            ['driver' => 'pdo_sqlite', 'memory' => true],
            ORMSetup::createAttributeMetadataConfiguration([__DIR__.'/Fixture'], true)
        );

        $schemaTool = new SchemaTool(self::$em);
        $schemaTool->createSchema(self::$em->getMetadataFactory()->getAllMetadata());

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
