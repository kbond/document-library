<?php

namespace Zenstruck\Document\Library\Tests\Namer;

use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\Namer\ExpressionNamer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ExpressionNamerTest extends TestCase
{
    /**
     * @test
     */
    public function generate_with_default_expression(): void
    {
        $namer = new ExpressionNamer();
        $library = self::inMemoryLibrary();

        $this->assertSame('foo-bar-9a0364b9e99bb480dd25e1f0284c8555', $namer->generateName($library->store('some/FoO BaR', 'content')));
        $this->assertSame('foo-bar-9a0364b9e99bb480dd25e1f0284c8555.txt', $namer->generateName($library->store('some/FoO BaR.txt', 'content')));
        $this->assertSame('foo-bar-9a0364b9e99bb480dd25e1f0284c8555.txt', $namer->generateName($library->store('some/FoO BaR.tXt', 'content')));
    }

    /**
     * @test
     */
    public function can_customize_default_expression(): void
    {
        $namer = new ExpressionNamer('{name}{ext}');
        $library = self::inMemoryLibrary();

        $this->assertSame('foo-bar', $namer->generateName($library->store('some/FoO BaR', 'content')));
        $this->assertSame('foo-bar.txt', $namer->generateName($library->store('some/FoO BaR.txt', 'content')));
        $this->assertSame('foo-bar.txt', $namer->generateName($library->store('some/FoO BaR.tXt', 'content')));
    }

    /**
     * @test
     */
    public function custom_expression(): void
    {
        $namer = new ExpressionNamer();
        $document = self::inMemoryLibrary()->store('some/pATh.txt', 'content');

        $this->assertSame(
            'a/prefix/path--9a0364b9e99bb480dd25e1f0284c8555.txt',
            $namer->generateName($document, ['expression' => 'a/prefix/{name}--{checksum}{ext}'])
        );
        $this->assertSame(
            'a/prefix/path--9a0364b.txt',
            $namer->generateName($document, ['expression' => 'a/prefix/{name}--{checksum:7}{ext}'])
        );
    }

    /**
     * @test
     */
    public function expression_with_rand(): void
    {
        $namer = new ExpressionNamer();
        $document = self::inMemoryLibrary()->store('some/pATh.txt', 'content');

        $name1 = $namer->generateName($document, ['expression' => '{rand}-{rand}']);
        $name2 = $namer->generateName($document, ['expression' => '{rand}-{rand}']);

        $this->assertMatchesRegularExpression('#^[0-9a-z]{6}-[0-9a-z]{6}$#', $name1);
        $this->assertMatchesRegularExpression('#^[0-9a-z]{6}-[0-9a-z]{6}$#', $name2);
        $this->assertNotSame($name1, $name2);
        $this->assertSame(13, \mb_strlen($name1));
    }

    /**
     * @test
     */
    public function can_customize_rand_length(): void
    {
        $namer = new ExpressionNamer();
        $document = self::inMemoryLibrary()->store('some/pATh.txt', 'content');

        $name1 = $namer->generateName($document, ['expression' => '{rand:3}-{rand:10}']);
        $name2 = $namer->generateName($document, ['expression' => '{rand:3}-{rand:10}']);

        $this->assertMatchesRegularExpression('#^[0-9a-z]{3}-[0-9a-z]{10}$#', $name1);
        $this->assertMatchesRegularExpression('#^[0-9a-z]{3}-[0-9a-z]{10}$#', $name2);
        $this->assertNotSame($name1, $name2);
        $this->assertSame(14, \mb_strlen($name1));
    }

    /**
     * @test
     */
    public function invalid_expression_variable(): void
    {
        $namer = new ExpressionNamer();
        $document = self::inMemoryLibrary()->store('some/pATh.txt', 'content');

        $this->expectException(\LogicException::class);
        $this->expectDeprecationMessage('Unable to parse expression variable {invalid}.');

        $namer->generateName($document, ['expression' => 'prefix/{invalid}']);
    }
}
