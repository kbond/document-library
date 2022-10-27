<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\Serializer;

use Psr\Container\ContainerInterface;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyDocumentNormalizer extends DocumentNormalizer
{
    public function __construct(private ContainerInterface $container)
    {
    }

    protected function registry(): LibraryRegistry
    {
        return $this->container->get(LibraryRegistry::class);
    }

    protected function namer(): Namer
    {
        return $this->container->get(Namer::class);
    }
}
