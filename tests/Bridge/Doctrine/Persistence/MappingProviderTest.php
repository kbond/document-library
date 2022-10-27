<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence;

use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;
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
                'namer' => 'expression:prefix/{this.name|slug}-{checksum:7}{ext}',
            ],
            'document2' => [
                'library' => 'memory',
                'metadata' => ['path', 'size'],
            ],
            'document3' => [
                'library' => 'memory',
                'namer' => 'expression:prefix/{this.name|slug}-{checksum:7}{ext}',
                'metadata' => ['checksum', 'extension'],
            ],
            'document5' => [
                'library' => 'memory',
                'metadata' => true,
            ],
            'document4' => [
                'library' => 'memory',
                'namer' => 'expression:prefix/{this.name|slug}.txt',
            ],
        ],
    ];

    /**
     * @test
     */
    public function can_get_mapping_for_class(): void
    {
        $this->assertSame(
            self::MAPPINGS[Entity1::class],
            \array_map(fn(Mapping $mapping) => $mapping->toArray(), $this->provider()->get(Entity1::class))
        );
    }

    /**
     * @test
     */
    public function can_get_all_mappings(): void
    {
        $this->assertSame(
            self::MAPPINGS,
            \array_map(
                fn(array $v) => \array_map(fn(Mapping $m) => $m->toArray(), $v),
                $this->provider()->all()
            )
        );
    }

    abstract protected function provider(): MappingProvider;
}
