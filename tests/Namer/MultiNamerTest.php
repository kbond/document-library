<?php

namespace Zenstruck\Document\Library\Tests\Namer;

use Zenstruck\Document;
use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\Namer\Expression;
use Zenstruck\Document\Namer\MultiNamer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MultiNamerTest extends TestCase
{
    /**
     * @test
     */
    public function default_namer(): void
    {
        $namer = new MultiNamer();

        $this->assertMatchesRegularExpression('#^foo-bar-[0-9a-z]{6}$#', $namer->generateName(self::$library->store('some/FoO BaR', 'content')));
        $this->assertMatchesRegularExpression('#^foo-bar-[0-9a-z]{6}\.txt$#', $namer->generateName(self::$library->store('some/FoO BaR.TxT', 'content')));
    }

    /**
     * @test
     */
    public function can_define_default_namer(): void
    {
        $namer = new MultiNamer(defaultContext: ['namer' => 'checksum']);

        $this->assertSame('9a0364b9e99bb480dd25e1f0284c8555.txt', $namer->generateName(self::$library->store('foo/bar.txt', 'content')));
    }

    /**
     * @test
     */
    public function namer_as_callable(): void
    {
        $namer = new MultiNamer();

        $this->assertSame('foo-baz.txt', $namer->generateName(self::$library->store('some/file.txt', 'content'), [
            'namer' => function(Document $document, array $context) {
                return "foo-{$context['bar']}.{$document->extension()}";
            },
            'bar' => 'baz',
        ]));
    }

    /**
     * @test
     */
    public function can_use_specific_namer(): void
    {
        $namer = new MultiNamer();

        $this->assertSame('foo-bar.txt', $namer->generateName(self::$library->store('some/FoO BaR.txt', ''), [
            'namer' => 'slugify',
        ]));
    }

    /**
     * @test
     */
    public function expression_namer(): void
    {
        $namer = new MultiNamer();

        $this->assertSame('foo-bar.txt', $namer->generateName(self::$library->store('some/FoO BaR.txt', ''), [
            'namer' => 'expression:{name}{ext}',
        ]));
        $this->assertSame('foo-bar.txt', $namer->generateName(self::$library->store('some/FoO BaR.txt', ''), [
            'namer' => new Expression('{name}{ext}'),
        ]));
    }

    /**
     * @test
     */
    public function cannot_use_invalid_namer(): void
    {
        $namer = new MultiNamer();

        $this->expectException(\InvalidArgumentException::class);

        $this->assertSame('foo-bar.txt', $namer->generateName(self::$library->store('some/FoO BaR.txt', ''), [
            'namer' => 'invalid',
        ]));
    }
}
