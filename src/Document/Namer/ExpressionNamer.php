<?php

namespace Zenstruck\Document\Namer;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ExpressionNamer extends BaseNamer
{
    public function __construct(
        private string $defaultExpression = '{name}-{checksum}{ext}',
        ?SluggerInterface $slugger = null,
        private ?PropertyAccessorInterface $accessor = null,
    ) {
        parent::__construct($slugger);
    }

    public function generateName(Document $document, array $context = []): string
    {
        return \preg_replace_callback(
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
            $context['expression'] ?? $this->defaultExpression
        );
    }

    private function parseVariable(string $variable, Document $document, array $context): string
    {
        if (2 === \count($parts = \explode(':', $variable))) {
            return match (\mb_strtolower($parts[0])) {
                'checksum' => \mb_substr($document->checksum(), 0, (int) $parts[1]),
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
            return $this->propertyAccessor()->getValue($document, \mb_substr($variable, 9));
        }

        if (\array_key_exists($variable, $context)) {
            return $context[$variable];
        }

        if (\str_contains($variable, '.') && !\str_starts_with($variable, '[')) {
            // normalize dot notation for object access
            $parts = \explode('.', $variable, 2);
            $parts[0] = "[{$parts[0]}]";
            $variable = \implode('.', $parts);
        }

        return $this->propertyAccessor()->getValue($context, $variable);
    }

    private function propertyAccessor(): PropertyAccessorInterface
    {
        if ($this->accessor) {
            return $this->accessor;
        }

        if (!\class_exists(PropertyAccessor::class)) {
            throw new \LogicException('symfony/property-access is required to parse nested context. Install with "composer require symfony/property-access".');
        }

        return $this->accessor = new PropertyAccessor();
    }
}
