<?php

namespace Zenstruck\Document\Namer;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChecksumNamer extends BaseNamer
{
    protected function generate(Document $document, array $context = []): string
    {
        return
            self::checksum($document, $context['alg'] ?? $context['algorithm'] ?? null, $context['length'] ?? null).
            self::extensionWithDot($document)
        ;
    }
}
