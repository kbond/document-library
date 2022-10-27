<?php

namespace Zenstruck\Document\Namer;

use Psr\Container\ContainerInterface;
use Zenstruck\Document;
use Zenstruck\Document\Namer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MultiNamer implements Namer
{
    private const DEFAULT_NAMER = 'expression';

    /** @var array<string,Namer> */
    private array $defaultNamers = [];

    /**
     * @param array<string,Namer> $namers
     */
    public function __construct(private ContainerInterface|array $namers = [], private array $defaultContext = [])
    {
    }

    public function generateName(Document $document, array $context = []): string
    {
        $context = \array_merge($this->defaultContext, $context);
        $namer = $context['namer'] ?? self::DEFAULT_NAMER;

        if (\is_callable($namer)) {
            return $namer($document, $context);
        }

        if (\str_starts_with($namer, 'expression:')) {
            $context['expression'] = \mb_substr($namer, 11);
            $context['namer'] = $namer = 'expression';
        }

        return $this->get($namer)->generateName($document, $context);
    }

    private function get(string $name): Namer
    {
        if (isset($this->defaultNamers[$name])) {
            return $this->defaultNamers[$name];
        }

        if (\is_array($this->namers) && isset($this->namers[$name])) {
            return $this->namers[$name];
        }

        if ($this->namers instanceof ContainerInterface && $this->namers->has($name)) {
            return $this->namers->get($name);
        }

        return $this->defaultNamers[$name] = match ($name) {
            'expression' => new ExpressionNamer(),
            'checksum' => new ChecksumNamer(),
            'slugify' => new SlugifyNamer(),
            default => throw new \InvalidArgumentException(\sprintf('Namer "%s" is not registered.', $name)),
        };
    }
}
