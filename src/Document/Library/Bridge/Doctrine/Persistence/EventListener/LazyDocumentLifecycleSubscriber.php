<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence\EventListener;

use Psr\Container\ContainerInterface;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\MappingProvider;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyDocumentLifecycleSubscriber extends DocumentLifecycleSubscriber
{
    public function __construct(private ContainerInterface $container)
    {
    }

    protected function registry(): LibraryRegistry
    {
        return $this->container->get(LibraryRegistry::class);
    }

    protected function mappingProvider(): MappingProvider
    {
        return $this->container->get(MappingProvider::class);
    }

    protected function namer(): Namer
    {
        return $this->container->get(Namer::class);
    }
}
