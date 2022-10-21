<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence;

use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\MappingProvider;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\HasORM;
use Zenstruck\Document\Library\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class MappingProviderTest extends TestCase
{
    use HasORM;

    /**
     * @test
     */
    public function can_get_mapping_for_class(): void
    {
        $this->assertSame(
            [
                'document1' => [
                    'library' => 'memory',
                ],
                'document2' => [
                    'library' => 'public',
                ],
            ],
            $this->provider()->get(Entity1::class)
        );
    }

    /**
     * @test
     */
    public function can_get_all_mappings(): void
    {
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
            $this->provider()->all()
        );
    }

    abstract protected function provider(): MappingProvider;
}
