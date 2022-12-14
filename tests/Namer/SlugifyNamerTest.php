<?php

namespace Zenstruck\Document\Library\Tests\Namer;

use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\Namer\SlugifyNamer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SlugifyNamerTest extends TestCase
{
    /**
     * @test
     */
    public function generate_name(): void
    {
        $namer = new SlugifyNamer();

        $this->assertSame('foo-bar', $namer->generateName(self::$library->store('some/FoO BaR', '')));
        $this->assertSame('foo-bar.txt', $namer->generateName(self::$library->store('some/FoO BaR.txt', '')));
        $this->assertSame('foo-bar.txt', $namer->generateName(self::$library->store('some/FoO BaR.tXt', '')));
    }
}
