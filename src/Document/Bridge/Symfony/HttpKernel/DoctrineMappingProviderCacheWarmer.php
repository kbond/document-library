<?php

namespace Zenstruck\Document\Bridge\Symfony\HttpKernel;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping\CacheMappingProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class DoctrineMappingProviderCacheWarmer implements CacheWarmerInterface
{
    public function __construct(private CacheMappingProvider $provider)
    {
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir): array
    {
        $this->provider->warm();

        return [];
    }
}
