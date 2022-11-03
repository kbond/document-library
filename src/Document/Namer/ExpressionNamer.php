<?php

namespace Zenstruck\Document\Namer;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ExpressionNamer extends BaseNamer
{
    private const DEFAULT_EXPRESSION = '{name}-{rand}{ext}';

    protected function generate(Document $document, array $context = []): string
    {
        return \preg_replace_callback( // @phpstan-ignore-line
            '#{([\w.:\-\[\]]+)(\|(slug|slugify|lower))?}#',
            function($matches) use ($document, $context) {
                $value = match ($matches[1]) {
                    'name' => $this->slugify($document->nameWithoutExtension()),
                    'ext' => self::extensionWithDot($document),
                    'checksum' => $document->checksum(),
                    'rand' => self::randomString(),
                    default => $this->parseVariable($matches[1], $document, $context),
                };

                return match ($matches[3] ?? null) {
                    'slug', 'slugify' => $this->slugify($value),
                    'lower' => \mb_strtolower($value),
                    default => $value,
                };
            },
            $context['expression'] ?? self::DEFAULT_EXPRESSION
        );
    }

    private function parseVariable(string $variable, Document $document, array $context): string
    {
        if (\count($parts = \explode(':', $variable)) > 1) {
            return match (\mb_strtolower($parts[0])) {
                'checksum' => self::parseChecksum($document, $parts),
                'rand' => self::randomString((int) $parts[1]),
                default => throw new \LogicException(\sprintf('Unable to parse expression variable {%s}.', $variable)),
            };
        }

        $value = $this->parseVariableValue($document, $variable, $context);

        if (null === $value || \is_scalar($value) || $value instanceof \Stringable) {
            return (string) $value;
        }

        throw new \LogicException(\sprintf('Unable to parse expression variable {%s}.', $variable));
    }

    private function parseVariableValue(Document $document, string $variable, array $context): mixed
    {
        if (\str_starts_with($variable, 'document.')) {
            return self::dotAccess($document, \mb_substr($variable, 9));
        }

        if (\array_key_exists($variable, $context)) {
            return $context[$variable];
        }

        return self::dotAccess($context, $variable);
    }

    /**
     * Quick and dirty "dot" accessor that works for objects and arrays.
     */
    private static function dotAccess(object|array &$what, string $path): mixed
    {
        $current = &$what;

        foreach (\explode('.', $path) as $segment) {
            if (\is_array($current) && \array_key_exists($segment, $current)) {
                $current = &$current[$segment];

                continue;
            }

            if (!\is_object($current)) {
                throw new \InvalidArgumentException(\sprintf('Unable to access "%s".', $path));
            }

            if (\method_exists($current, $segment)) {
                $current = $current->{$segment}();

                continue;
            }

            foreach (['get', 'has', 'is'] as $prefix) {
                if (\method_exists($current, $method = $prefix.\ucfirst($segment))) {
                    $current = $current->{$method}();

                    continue 2;
                }
            }

            if (\property_exists($current, $segment)) {
                $current = &$current->{$segment};

                continue;
            }

            throw new \InvalidArgumentException(\sprintf('Unable to access "%s".', $path));
        }

        return $current;
    }

    private static function parseChecksum(Document $document, array $parts): string
    {
        unset($parts[0]); // removes "checksum"

        foreach ($parts as $part) {
            match (true) {
                \is_numeric($part) => $length = (int) $part,
                default => $algorithm = $part,
            };
        }

        return self::checksum($document, $algorithm ?? null, $length ?? null);
    }
}
