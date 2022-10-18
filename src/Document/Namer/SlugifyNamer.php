<?php

namespace Zenstruck\Document\Namer;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SlugifyNamer extends BaseNamer
{
    public function generateName(Document $document, array $context = []): string
    {
        return $this->slugify($document->nameWithoutExtension()).self::extensionWithDot($document);
    }
}
