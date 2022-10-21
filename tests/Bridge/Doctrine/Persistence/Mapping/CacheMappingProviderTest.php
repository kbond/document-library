<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence\Mapping;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping\CacheMappingProvider;
use Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping\ManagerRegistryMappingProvider;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence\MappingProviderTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CacheMappingProviderTest extends MappingProviderTest
{
    /**
     * @test
     */
    public function get_is_cached(): void
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())->method('getManagerForClass')->withAnyParameters()->willReturn($this->em());

        $provider = $this->provider($adapter = new ArrayAdapter(), $doctrine);
        $provider->get(Entity1::class);

        $provider = $this->provider($adapter, $doctrine);
        $provider->get(Entity1::class);
        $provider->get(Entity1::class);
    }

    /**
     * @test
     */
    public function can_be_warmed(): void
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())->method('getManagers')->withAnyParameters()->willReturn($this->em());

        $provider = $this->provider($adapter = new ArrayAdapter(), $doctrine);
        $provider->warm();

        $provider = $this->provider($adapter, $doctrine);
        $provider->get(Entity1::class);
        $provider->get(Entity1::class);
    }

    protected function provider(?CacheInterface $cache = null, ?ManagerRegistry $registry = null): CacheMappingProvider
    {
        return new CacheMappingProvider(
            $cache ?? new NullAdapter(),
            new ManagerRegistryMappingProvider($registry ?? $this->doctrine())
        );
    }
}
