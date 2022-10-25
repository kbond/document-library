<?php

namespace Zenstruck\Document\Namer;

use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ExpressionNamer extends BaseNamer
{
    public function __construct(private string $defaultExpression = '{name}-{checksum}{ext}', ?SluggerInterface $slugger = null)
    {
        parent::__construct($slugger);
    }

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
            $context['expression'] ?? $this->defaultExpression
        );
    }
}
