<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Zenstruck\Document\Library;
use Zenstruck\Document\Library\FlysystemLibrary;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckDocumentLibraryExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        if (!$mergedConfig['libraries']) {
            return;
        }

        foreach ($mergedConfig['libraries'] as $name => $service) {
            $container->register($id = '.zenstruck_document.library.'.$name, FlysystemLibrary::class)
                ->setArguments([new Reference($service)])
                ->addTag('document_library', ['key' => $name])
            ;

            $container->registerAliasForArgument($id, Library::class, $name);
        }

        $container->register(LibraryRegistry::class)
            ->setArguments([
                new ServiceLocatorArgument(new TaggedIteratorArgument('document_library', 'key', needsIndexes: true)),
            ])
        ;
    }
}
