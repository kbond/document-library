<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence\EventListener;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\EventListener\DocumentLifecycleSubscriber;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\EventListener\LazyDocumentLifecycleSubscriber;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping\ManagerRegistryMappingProvider;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\MappingProvider;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyDocumentLifecycleSubscriberTest extends DocumentLifecycleSubscriberTest
{
    protected function createSubscriber(LibraryRegistry $registry): DocumentLifecycleSubscriber
    {
        return new LazyDocumentLifecycleSubscriber(new ServiceLocator([
            LibraryRegistry::class => fn() => $registry,
            MappingProvider::class => fn() => new ManagerRegistryMappingProvider($this->doctrine()),
        ]));
    }
}
