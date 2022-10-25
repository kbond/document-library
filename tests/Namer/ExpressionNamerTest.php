<?php

namespace Zenstruck\Document\Library\Tests\Namer;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
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
    public function can_use_context_as_expression_variables(): void
    {
        $namer = new ExpressionNamer();
        $document = self::inMemoryLibrary()->store('some/pATh.txt', 'content');

        $this->assertSame(
            'prefix/baz/value/stRIng/1//prop1-valUe/6',
            $namer->generateName($document, [
                'expression' => 'prefix/{foo.bar}/{[array][key]}/{object}/{[object].prop1}/{object.prop2}/{object.prop3}/{object.prop4}{object.prop5}',
                'foo.bar' => 'baz',
                'array' => ['key' => 'value'],
                'object' => new ContextObject(),
            ])
        );
    }

    /**
     * @test
     */
    public function can_access_raw_document_values(): void
    {
        $namer = new ExpressionNamer();
        $document = self::inMemoryLibrary()->store('some/pATh.tXt', 'content');

        $this->assertSame('prefix/9a0364b9e99bb480dd25e1f0284c8555-pATh.tXt', $namer->generateName($document, [
            'expression' => 'prefix/{document.checksum}-{document.name}',
        ]));
    }

    /**
     * @test
     */
    public function can_use_variable_modifiers(): void
    {
        $namer = new ExpressionNamer();
        $document = self::inMemoryLibrary()->store('some/pA Th.tXt', 'content');

        $this->assertSame('prefix/string/prop1-value/pa-th--9a0364b.txt', $namer->generateName($document, [
            'expression' => 'prefix/{object|slug}/{object.prop3|lower}/{document.nameWithoutExtension|slug}--{checksum:7|lower}{ext}',
            'object' => new ContextObject(),
        ]));
    }

    /**
     * @test
     */
    public function invalid_expression_variable(): void
    {
        $namer = new ExpressionNamer();
        $document = self::inMemoryLibrary()->store('some/pATh.txt', 'content');

        $this->expectException(NoSuchPropertyException::class);

        $namer->generateName($document, ['expression' => 'prefix/{invalid}']);
    }
}

class ContextObject
{
    public $prop1 = true;
    public $prop2 = false;
    private $prop3 = 'prop1-valUe';
    private $prop4 = 6;
    private $prop5;

    public function __toString(): string
    {
        return 'stRIng';
    }

    public function getProp3(): string
    {
        return $this->prop3;
    }

    public function getProp4(): int
    {
        return $this->prop4;
    }

    public function getProp5()
    {
        return $this->prop5;
    }
}
