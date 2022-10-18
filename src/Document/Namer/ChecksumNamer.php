<?php

namespace Zenstruck\Document\Namer;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChecksumNamer extends BaseNamer
{
    public function generateName(Document $document, array $context = []): string
    {
        // todo customize algorithm
        return $document->checksum().self::extensionWithDot($document);
    }
}
