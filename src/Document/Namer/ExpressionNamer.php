<?php

namespace Zenstruck\Document\Namer;

use Symfony\Component\String\ByteString;
use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ExpressionNamer extends BaseNamer
{
    public function generateName(Document $document, array $context = []): string
    {
        return \preg_replace_callback(
            '#{(name|ext|checksum|rand)}#',
            function($matches) use ($document) {
                return match ($matches[0]) {
                    '{name}' => $this->slugify($document->nameWithoutExtension()),
                    '{ext}' => self::extensionWithDot($document),
                    '{checksum}' => $document->checksum(),
                    '{rand}' => self::randomString(),
                    default => throw new \LogicException('Invalid match.'),
                };
            },
            $context['expression'] ?? '{name}-{rand}{ext}'
        );
    }

    private static function randomString(): string
    {
        return ByteString::fromRandom(6, '123456789abcdefghijkmnopqrstuvwxyz')->toString();
    }
}
