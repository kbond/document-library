<?php

namespace Zenstruck\Document\Namer;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChecksumNamer extends BaseNamer
{
    /**
     * @param array{
     *     alg? :string,
     *     length?: int
     * } $context
     */
    public function generateName(Document $document, array $context = []): string
    {
        $checksum = $document->checksum($context['alg'] ?? []);

        if (isset($context['length'])) {
            $checksum = \substr($checksum, 0, $context['length']);
        }

        return $checksum.self::extensionWithDot($document);
    }
}
