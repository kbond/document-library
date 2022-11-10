<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Document\Library;
use Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Entity\Entity1;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DoctrineIntegrationTest extends KernelTestCase
{
    use ResetDatabase;

    /**
     * @test
     */
    public function doctrine_lifecycle(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /** @var Library $library */
        $library = self::getContainer()->get(LibraryRegistry::class)->get('public');

        $entity = new Entity1();
        $entity->document1 = $library->store('some/file.txt', 'content');
        $em->persist($entity);
        $em->flush();
        $em->clear();

        $entity = $em->find(Entity1::class, 1);

        $this->assertSame('some/file.txt', $entity->document1->path());
        $this->assertTrue($library->has('some/file.txt'));

        $entity->document1 = $library->store('another/file.txt', 'content');
        $em->flush();

        $this->assertTrue($library->has('another/file.txt'));
        $this->assertFalse($library->has('some/file.txt'));

        $em->remove($entity);
        $em->flush();

        $this->assertFalse($library->has('another/file.txt'));
        $this->assertFalse($library->has('some/file.txt'));
    }
}
