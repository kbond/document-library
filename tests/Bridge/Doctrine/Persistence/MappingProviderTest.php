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

    private const MAPPINGS = [
        Entity1::class => [
            'document1' => [
                'library' => 'memory',
                'expression' => 'prefix/{this.name|slug}-{checksum:7}{ext}',
            ],
            'document2' => [
                'library' => 'memory',
                'metadata' => ['path', 'size'],
            ],
            'document3' => [
                'library' => 'memory',
                'metadata' => ['checksum', 'extension'],
                'expression' => 'prefix/{this.name|slug}-{checksum:7}{ext}',
            ],
        ],
    ];

    /**
     * @test
     */
    public function can_get_mapping_for_class(): void
    {
        $this->assertSame(self::MAPPINGS[Entity1::class], $this->provider()->get(Entity1::class));
    }

    /**
     * @test
     */
    public function can_get_all_mappings(): void
    {
        $this->assertSame(self::MAPPINGS, $this->provider()->all());
    }

    abstract protected function provider(): MappingProvider;
}
