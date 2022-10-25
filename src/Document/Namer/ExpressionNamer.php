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
            '#{([\w.:\-\[\]]+)}#',
            function($matches) use ($document, $context) {
                return match ($matches[1]) {
                    'name' => $this->slugify($document->nameWithoutExtension()),
                    'ext' => self::extensionWithDot($document),
                    'checksum' => $document->checksum(),
                    'rand' => self::randomString(),
                    default => $this->parseVariable($matches[1], $document, $context),
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

        $value = $this->parseContextVariable($variable, $context);

        if (null === $value || \is_scalar($value) || $value instanceof \Stringable) {
            return $this->slugify((string) $value);
        }

        throw new \LogicException(\sprintf('Unable to parse expression variable {%s}.', $variable));
    }

    private function parseContextVariable(string $variable, array $context): mixed
    {
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
