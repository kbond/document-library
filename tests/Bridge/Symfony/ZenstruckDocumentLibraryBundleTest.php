<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Service;

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
}
