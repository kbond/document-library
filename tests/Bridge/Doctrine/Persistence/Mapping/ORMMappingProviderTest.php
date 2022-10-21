<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Persistence\Mapping;

use Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping\ORMMappingProvider;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture\Entity1;
use Zenstruck\Document\Library\Tests\Bridge\Doctrine\HasORM;
use Zenstruck\Document\Library\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ORMMappingProviderTest extends TestCase
{
    use HasORM;

    /**
     * @test
     */
    public function can_get_mapping_for_class(): void
    {
        $provider = new ORMMappingProvider($this->doctrine());

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
}
