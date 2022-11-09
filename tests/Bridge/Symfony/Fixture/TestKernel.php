<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;
use Zenstruck\Document\Library\Bridge\Symfony\ZenstruckDocumentLibraryBundle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new ZenstruckDocumentLibraryBundle();
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'router' => ['utf8' => true],
            'test' => true,
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
    }
}
