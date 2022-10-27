<?php

namespace Zenstruck\Document\Namer;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SlugifyNamer extends BaseNamer
{
    protected function generate(Document $document, array $context = []): string
    {
        return $this->slugify($document->nameWithoutExtension()).self::extensionWithDot($document);
    }
}
