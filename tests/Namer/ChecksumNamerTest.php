<?php

namespace Zenstruck\Document\Library\Tests\Namer;

use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\Namer\ChecksumNamer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChecksumNamerTest extends TestCase
{
    /**
     * @test
     */
    public function generate_name(): void
    {
        $namer = new ChecksumNamer();
        $library = self::inMemoryLibrary();

        $this->assertSame('9a0364b9e99bb480dd25e1f0284c8555', $namer->generateName($library->store('foo/bar', 'content')));
        $this->assertSame('9a0364b9e99bb480dd25e1f0284c8555.txt', $namer->generateName($library->store('foo/bar.txt', 'content')));
        $this->assertSame('9a0364b9e99bb480dd25e1f0284c8555.txt', $namer->generateName($library->store('foo/bar.TxT', 'content')));
        $this->assertSame('040f06fd774092478d450774f5ba30c5da78acc8.txt', $namer->generateName($library->store('foo/bar.TxT', 'content'), ['alg' => 'sha1']));
        $this->assertSame('040f06f.txt', $namer->generateName($library->store('foo/bar.TxT', 'content'), ['alg' => 'sha1', 'length' => 7]));
    }
}
