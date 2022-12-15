<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Zenstruck\Document\Library\Bridge\Symfony\ZenstruckDocumentLibraryBundle;
use Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller\ArgumentResolverController;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new ZenstruckFoundryBundle();
        yield new ZenstruckDocumentLibraryBundle();
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/Controller', 'annotation');
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'router' => ['utf8' => true],
            'test' => true,
        ]);

        $c->loadFromExtension('doctrine', [
            'dbal' => ['url' => 'sqlite:///%kernel.project_dir%/var/data.db'],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
                'mappings' => [
                    'Test' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Bridge/Symfony/Fixture/Entity',
                        'prefix' => 'Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Entity',
                        'alias' => 'Test',
                    ],
                ],
            ],
        ]);

        $c->loadFromExtension('zenstruck_document_library', [
            'libraries' => [
                'public' => 'public.filesystem',
                'private' => 'private.filesystem',
            ],
        ]);

        $c->register('public.adapter', InMemoryFilesystemAdapter::class);
        $c->register('public.filesystem', Filesystem::class)
            ->setArguments([new Reference('public.adapter')])
        ;
        $c->register('private.adapter', InMemoryFilesystemAdapter::class);
        $c->register('private.filesystem', Filesystem::class)
            ->setArguments([new Reference('private.adapter')])
        ;

        $c->register(Service::class)
            ->setPublic(true)
            ->setAutowired(true)
        ;

        $c->register(ArgumentResolverController::class)->addTag('controller.service_arguments');
    }
}
