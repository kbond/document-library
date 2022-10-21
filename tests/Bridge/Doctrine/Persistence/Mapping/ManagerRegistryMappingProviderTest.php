<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence\Mapping;

use Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping\ManagerRegistryMappingProvider;
use Zenstruck\Document\Bridge\Doctrine\Persistence\MappingProvider;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence\MappingProviderTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ManagerRegistryMappingProviderTest extends MappingProviderTest
{
    protected function provider(): MappingProvider
    {
        return new ManagerRegistryMappingProvider($this->doctrine());
    }
}
