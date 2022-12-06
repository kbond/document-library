<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\DependencyInjection;

use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Zenstruck\Document\Library;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\EventListener\LazyDocumentLifecycleSubscriber;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping\CacheMappingProvider;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping\ManagerRegistryMappingProvider;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\MappingProvider;
use Zenstruck\Document\Library\Bridge\Symfony\Form\DocumentType;
use Zenstruck\Document\Library\Bridge\Symfony\Form\PendingDocumentType;
use Zenstruck\Document\Library\Bridge\Symfony\HttpKernel\DoctrineMappingProviderCacheWarmer;
use Zenstruck\Document\Library\Bridge\Symfony\Serializer\LazyDocumentNormalizer;
use Zenstruck\Document\Library\Bridge\Symfony\ValueResolver\PendingDocumentValueResolver;
use Zenstruck\Document\Library\FlysystemLibrary;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer;
use Zenstruck\Document\Namer\ChecksumNamer;
use Zenstruck\Document\Namer\ExpressionNamer;
use Zenstruck\Document\Namer\MultiNamer;
use Zenstruck\Document\Namer\SlugifyNamer;

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

        // libraries
        foreach ($mergedConfig['libraries'] as $name => $service) {
            $container->register($id = '.zenstruck_document.library.'.$name, FlysystemLibrary::class)
                ->addArgument(new Reference($service))
                ->addTag('document_library', ['key' => $name])
            ;

            $container->registerAliasForArgument($id, Library::class, $name);
        }

        $container->register(LibraryRegistry::class)
            ->addArgument(
                new ServiceLocatorArgument(new TaggedIteratorArgument('document_library', 'key', needsIndexes: true)),
            )
        ;

        // namers
        $container->register('.zenstruck_document.namer.slugify', SlugifyNamer::class)
            ->addArgument(new Reference('slugger', ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->addTag('document_namer', ['key' => 'slugify'])
        ;
        $container->register('.zenstruck_document.namer.checksum', ChecksumNamer::class)
            ->addArgument(new Reference('slugger', ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->addTag('document_namer', ['key' => 'checksum'])
        ;
        $container->register('.zenstruck_document.namer.expression', ExpressionNamer::class)
            ->addArgument(new Reference('slugger', ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->addTag('document_namer', ['key' => 'expression'])
        ;
        $container->register(Namer::class, MultiNamer::class)
            ->addArgument(
                new ServiceLocatorArgument(new TaggedIteratorArgument('document_namer', 'key', needsIndexes: true)),
            )
        ;

        // normalizer
        $container->register('.zenstruck_document.normalizer', LazyDocumentNormalizer::class)
            ->addArgument(new ServiceLocatorArgument([
                LibraryRegistry::class => new Reference(LibraryRegistry::class),
                Namer::class => new Reference(Namer::class),
            ]))
            ->addTag('serializer.normalizer')
        ;

        // form types
        $container->register('.zenstruck_document.form.pending_document_type', PendingDocumentType::class)
            ->addTag('form.type')
        ;
        $container->register('.zenstruck_document.form.document_type', DocumentType::class)
            ->setArguments([new Reference(LibraryRegistry::class), new Reference(Namer::class)])
            ->addTag('form.type')
        ;

        // value resolver
        $container->register('.zenstruck_document.value_resolver.pending_document', PendingDocumentValueResolver::class)
            ->addTag('controller.argument_value_resolver', ['priority' => 110])
        ;

        if (isset($container->getParameter('kernel.bundles')['DoctrineBundle'])) {
            $this->configureDoctrine($container);
        }
    }

    private function configureDoctrine(ContainerBuilder $container): void
    {
        $container->register('.zenstruck_document.doctrine.mapping_provider', ManagerRegistryMappingProvider::class)
            ->addArgument(new Reference('doctrine'))
        ;
        $container->register('.zenstruck_document.doctrine.cache_mapping_provider', CacheMappingProvider::class)
            ->setDecoratedService('.zenstruck_document.doctrine.mapping_provider')
            ->setArguments([new Reference('cache.system'), new Reference('.inner')])
        ;
        $container->register('.zenstruck_document.doctrine.cache_mapping_provider_warmer', DoctrineMappingProviderCacheWarmer::class)
            ->addArgument(new Reference('.zenstruck_document.doctrine.cache_mapping_provider'))
            ->addTag('kernel.cache_warmer')
        ;
        $container->register('.zenstruck_document.doctrine.subscriber', LazyDocumentLifecycleSubscriber::class)
            ->addArgument(new ServiceLocatorArgument([
                LibraryRegistry::class => new Reference(LibraryRegistry::class),
                Namer::class => new Reference(Namer::class),
                MappingProvider::class => new Reference('.zenstruck_document.doctrine.mapping_provider'),
            ]))
            ->addTag('doctrine.event_listener', ['event' => Events::postLoad])
            ->addTag('doctrine.event_listener', ['event' => Events::prePersist])
            ->addTag('doctrine.event_listener', ['event' => Events::preUpdate])
            ->addTag('doctrine.event_listener', ['event' => Events::postRemove])
            ->addTag('doctrine.event_listener', ['event' => Events::postFlush])
            ->addTag('doctrine.event_listener', ['event' => Events::onClear])
        ;
    }
}
