<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence\EventListener;

use Doctrine\ORM\Events;
use League\Flysystem\UnableToReadFile;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\EventListener\DocumentLifecycleSubscriber;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping\ManagerRegistryMappingProvider;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\HasORM;
use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer\MultiNamer;
use Zenstruck\Document\PendingDocument;
use Zenstruck\Document\TempFile;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DocumentLifecycleSubscriberTest extends TestCase
{
    use HasORM;

    protected function setUp(): void
    {
        parent::setUp();

        $subscriber = $this->createSubscriber(self::$libraryRegistry);
        $events = [Events::postFlush, Events::prePersist, Events::preUpdate, Events::postRemove, Events::postLoad];

        foreach ($events as $event) {
            $this->em()->getEventManager()->addEventListener($event, $subscriber);
        }
    }

    /**
     * @test
     */
    public function can_auto_load_documents(): void
    {
        $entity = new Entity1();
        $entity->document1 = self::$libraryRegistry->get('memory')->store('some/file.txt', 'content');
        $this->em()->persist($entity);
        $this->em()->flush();

        $this->assertSame('content', $entity->document1->contents());

        $this->em()->clear();
        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertSame('content', $entity->document1->contents());
    }

    /**
     * @test
     */
    public function documents_can_be_persisted_and_updated_with_metadata(): void
    {
        $library = self::$libraryRegistry->get('memory');

        $entity = new Entity1();
        $entity->document2 = $library->store('some/file.txt', 'content');
        $this->em()->persist($entity);
        $this->em()->flush();

        $this->assertSame('some/file.txt', $entity->document2->path());
        $this->assertSame(7, $entity->document2->size());
        $this->assertSame('content', $entity->document2->contents());

        $this->em()->clear();
        $library->store('some/file.txt', 'new content');

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertSame('some/file.txt', $entity->document2->path());
        $this->assertSame(7, $entity->document2->size()); // was cached in db
        $this->assertSame('new content', $entity->document2->contents());
        $this->assertSame(11, $entity->document2->size()); // should be refreshed when accessing contents

        $entity->document2 = $library->store('another/file.txt', 'something else');
        $this->em()->flush();

        $this->em()->clear();
        $library->store('another/file.txt', 'something else again');

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertSame('another/file.txt', $entity->document2->path());
        $this->assertSame(14, $entity->document2->size()); // was cached in db
        $this->assertSame('something else again', $entity->document2->contents());
        $this->assertSame(20, $entity->document2->size()); // should be refreshed when accessing contents
    }

    /**
     * @test
     */
    public function documents_can_be_saved_with_all_metadata(): void
    {
        $library = self::$libraryRegistry->get('memory');

        $entity = new Entity1();
        $entity->document5 = $library->store('some/file.txt', 'content');
        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $library->delete('some/file.txt');

        $lastModified = $entity->document5->lastModified();
        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertSame('some/file.txt', $entity->document5->path());
        $this->assertSame('file.txt', $entity->document5->name());
        $this->assertSame('file', $entity->document5->nameWithoutExtension());
        $this->assertSame('txt', $entity->document5->extension());
        $this->assertSame($lastModified, $entity->document5->lastModified());
        $this->assertSame(7, $entity->document5->size());
        $this->assertSame('9a0364b9e99bb480dd25e1f0284c8555', $entity->document5->checksum());
        $this->assertSame('text/plain', $entity->document5->mimeType());

        $this->expectException(UnableToReadFile::class);
        $entity->document5->contents();
    }

    /**
     * @test
     */
    public function documents_can_update_their_metadata(): void
    {
        $library = self::$libraryRegistry->get('memory');

        $entity = new Entity1();
        $entity->document2 = $library->store('some/file.txt', 'content');
        $this->em()->persist($entity);
        $this->em()->flush();

        $this->assertSame('some/file.txt', $entity->document2->path());
        $this->assertSame(7, $entity->document2->size());
        $this->assertSame('content', $entity->document2->contents());

        $library->store('some/file.txt', 'new content');

        $entity->document2 = clone $entity->document2->refresh(); // NOTE: clone is required
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertSame('some/file.txt', $entity->document2->path());
        $this->assertSame(11, $entity->document2->size());
        $this->assertSame('new content', $entity->document2->contents());
    }

    /**
     * @test
     */
    public function documents_are_deleted_on_update_to_new_document(): void
    {
        $library = self::$libraryRegistry->get('memory');

        $entity = new Entity1();
        $entity->document1 = $library->store('some/file.txt', 'content');
        $this->em()->persist($entity);
        $this->em()->flush();

        $this->assertTrue($library->has('some/file.txt'));

        $entity->document1 = null;
        $this->em()->flush();

        $this->assertFalse($library->has('some/file.txt'));
    }

    /**
     * @test
     */
    public function documents_are_deleted_on_remove(): void
    {
        $library = self::$libraryRegistry->get('memory');

        $entity = new Entity1();
        $entity->document1 = $library->store('some/file.txt', 'content');
        $this->em()->persist($entity);
        $this->em()->flush();

        $this->assertTrue($library->has('some/file.txt'));

        $this->em()->remove($entity);
        $this->em()->flush();

        $this->assertFalse($library->has('some/file.txt'));
    }

    /**
     * @test
     */
    public function can_persist_and_update_with_pending_file(): void
    {
        $library = self::$libraryRegistry->get('memory');

        $entity = new Entity1();
        $entity->name = 'Foo BaR';
        $entity->document1 = new PendingDocument(__FILE__);
        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $this->assertTrue($library->has($expectedPath = \sprintf('prefix/foo-bar-%s.php', \mb_substr(\md5_file(__FILE__), 0, 7))));

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertTrue($entity->document1->exists());
        $this->assertSame($expectedPath, $entity->document1->path());
        $this->assertSame(\file_get_contents(__FILE__), $entity->document1->contents());

        $entity->document1 = new PendingDocument(TempFile::for('content'));
        $entity->name = 'new name';
        $this->em()->flush();
        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertFalse($library->has($expectedPath));
        $this->assertTrue($library->has($expectedPath = 'prefix/new-name-9a0364b'));

        $this->assertTrue($entity->document1->exists());
        $this->assertSame($expectedPath, $entity->document1->path());
        $this->assertSame('content', $entity->document1->contents());
    }

    /**
     * @test
     */
    public function can_lazily_load_with_name(): void
    {
        $library = self::$libraryRegistry->get('memory');

        $entity = new Entity1();
        $entity->name = 'Foo BaR';
        $entity->document3 = new PendingDocument(__FILE__);
        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $this->assertTrue($library->has($expectedPath = \sprintf('prefix/foo-bar-%s.php', \mb_substr(\md5_file(__FILE__), 0, 7))));

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertSame($expectedPath, $entity->document3->path());
        $this->assertSame(\file_get_contents(__FILE__), $entity->document3->contents());
    }

    /**
     * @test
     */
    public function can_load_lazy_documents(): void
    {
        $library = self::$libraryRegistry->get('memory');

        $library->store($expectedPath = 'prefix/foo-bar.txt', 'content');

        $this->assertTrue($library->has($expectedPath));

        $entity = new Entity1();
        $entity->name = 'Foo Bar';
        $this->assertNull($entity->document4());

        $this->em()->persist($entity);
        $this->em()->flush();

        $this->assertSame($expectedPath, $entity->document4()?->path());

        $this->em()->clear();

        $entity = $this->em()->find(Entity1::class, 1);

        $this->assertSame($expectedPath, $entity->document4()?->path());

        $this->em()->remove($entity);
        $this->em()->flush();

        $this->assertFalse($library->has($expectedPath));
    }

    protected function createSubscriber(LibraryRegistry $registry): DocumentLifecycleSubscriber
    {
        return new DocumentLifecycleSubscriber(
            $registry,
            new ManagerRegistryMappingProvider($this->doctrine()),
            new MultiNamer(),
        );
    }
}
