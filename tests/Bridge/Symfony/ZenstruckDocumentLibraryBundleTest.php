<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Service;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer\MultiNamer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckDocumentLibraryBundleTest extends KernelTestCase
{
    /**
     * @test
     */
    public function can_autowire_libraries(): void
    {
        /** @var Service $service */
        $service = self::getContainer()->get(Service::class);

        $service->public->store('file1.txt', 'content');
        $service->private->store('file2.txt', 'content');

        $this->assertTrue($service->public->has('file1.txt'));
        $this->assertFalse($service->public->has('file2.txt'));
        $this->assertTrue($service->private->has('file2.txt'));
        $this->assertFalse($service->private->has('file1.txt'));
        $this->assertSame($service->public->open('file1.txt')->contents(), $service->registry->get('public')->open('file1.txt')->contents());
        $this->assertSame($service->private->open('file2.txt')->contents(), $service->registry->get('private')->open('file2.txt')->contents());
    }

    /**
     * @test
     */
    public function can_autowire_namer(): void
    {
        /** @var Service $service */
        $service = self::getContainer()->get(Service::class);

        $this->assertInstanceOf(MultiNamer::class, $service->namer);
    }

    /**
     * @test
     */
    public function can_use_normalizer(): void
    {
        /** @var LibraryRegistry $registry */
        $registry = self::getContainer()->get(LibraryRegistry::class);

        /** @var SerializerInterface $serializer */
        $serializer = self::getContainer()->get(SerializerInterface::class);

        $document = $registry->get('public')->store('some/file.txt', 'content');
        $document = $serializer->deserialize($serializer->serialize($document, 'json'), Document::class, 'json', [
            'library' => 'public',
        ]);

        $this->assertInstanceOf(LazyDocument::class, $document);
        $this->assertSame('some/file.txt', $document->path());
        $this->assertSame('content', $document->contents());
    }
}
