<?php

namespace Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping;

use Symfony\Contracts\Cache\CacheInterface;
use Zenstruck\Document\Bridge\Doctrine\Persistence\MappingProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CacheMappingProvider implements MappingProvider
{
    /** @var array<class-string,array<string,array<string,mixed>>> */
    private array $memoryCache = [];

    public function __construct(private CacheInterface $cache, private MappingProvider $inner)
    {
    }

    public function get(string $class): array
    {
        return $this->memoryCache[$class] ??= $this->cache->get(self::createKey($class), fn() => $this->inner->get($class));
    }

    public function all(): array
    {
        return $this->inner->all();
    }

    public function warm(): void
    {
        foreach ($this->all() as $class => $config) {
            $this->cache->get(self::createKey($class), fn() => $config, \INF);
        }
    }

    /**
     * @param class-string $class
     */
    private static function createKey(string $class): string
    {
        return 'zs_doc_map_'.\str_replace('\\', '', $class);
    }
}
