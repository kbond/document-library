<?php

namespace Zenstruck\Document\Namer;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Document;
use Zenstruck\Document\Namer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class BaseNamer implements Namer
{
    public function __construct(private ?SluggerInterface $slugger = null)
    {
    }

    final protected static function extensionWithDot(Document $document): string
    {
        return '' === ($ext = $document->extension()) ? '' : '.'.\mb_strtolower($ext);
    }

    final protected function slugify(string $value): string
    {
        return ($this->slugger ??= new AsciiSlugger())->slug($value);
    }
}
