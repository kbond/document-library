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
            '#{([\w.:\-]+)}#',
            function($matches) use ($document, $context) {
                return match ($matches[1]) {
                    'name' => $this->slugify($document->nameWithoutExtension()),
                    'ext' => self::extensionWithDot($document),
                    'checksum' => $document->checksum(),
                    'rand' => self::randomString(),
                    default => self::parseVariable($matches[1], $document, $context),
                };
            },
            $context['expression'] ?? $this->defaultExpression
        );
    }

    private static function parseVariable(string $variable, Document $document, array $context): string
    {
        if (2 === \count($parts = \explode(':', $variable))) {
            return match (\mb_strtolower($parts[0])) {
                'checksum' => \mb_substr($document->checksum(), 0, (int) $parts[1]),
                'rand' => self::randomString((int) $parts[1]),
                default => throw new \LogicException(\sprintf('Unable to parse expression variable {%s}.', $variable)),
            };
        }

        throw new \LogicException(\sprintf('Unable to parse expression variable {%s}.', $variable));
    }
}
