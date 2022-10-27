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
    /** @var array<string,Namer> */
    private array $defaultNamers = [];

    /**
     * @param array<string,Namer> $namers
     */
    public function __construct(private ContainerInterface|array $namers = [], private string $defaultNamer = 'expression')
    {
    }

    public function generateName(Document $document, array $context = []): string
    {
        $namer = $context['namer'] ?? $this->defaultNamer;

        if (\is_callable($namer)) {
            return $namer($document, $context);
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
