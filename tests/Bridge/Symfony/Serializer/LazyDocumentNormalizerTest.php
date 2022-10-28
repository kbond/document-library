<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Serializer;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Zenstruck\Document\Library\Bridge\Symfony\Serializer\DocumentNormalizer;
use Zenstruck\Document\Library\Bridge\Symfony\Serializer\LazyDocumentNormalizer;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer;
use Zenstruck\Document\Namer\MultiNamer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyDocumentNormalizerTest extends DocumentNormalizerTest
{
    protected static function normalizer(): DocumentNormalizer
    {
        return new LazyDocumentNormalizer(new ServiceLocator([
            LibraryRegistry::class => self::$libraryRegistry,
            Namer::class => new MultiNamer(),
        ]));
    }
}
