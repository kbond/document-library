<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence\Mapping;

use Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping\ManagerRegistryMappingProvider;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\HasORM;
use Zenstruck\Document\Library\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ManagerRegistryMappingProviderTest extends TestCase
{
    use HasORM;

    /**
     * @test
     */
    public function can_get_mapping_for_class(): void
    {
        $provider = new ManagerRegistryMappingProvider($this->doctrine());

        $this->assertSame(
            [
                'document1' => [
                    'library' => 'memory',
                ],
                'document2' => [
                    'library' => 'public',
                ],
            ],
            $provider->get(Entity1::class)
        );
    }

    /**
     * @test
     */
    public function can_get_all_mappings(): void
    {
        $provider = new ManagerRegistryMappingProvider($this->doctrine());

        $this->assertSame(
            [
                Entity1::class => [
                    'document1' => [
                        'library' => 'memory',
                    ],
                    'document2' => [
                        'library' => 'public',
                    ],
                ],
            ],
            $provider->all()
        );
    }
}
