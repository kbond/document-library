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
    private ContainerInterface|array $namers;

    /**
     * @param array<string,Namer> $namers
     */
    public function __construct(ContainerInterface|array $namers = [], private string $defaultNamer = 'expression')
    {
        $this->namers = $namers ?: [
            'expression' => new ExpressionNamer(),
            'checksum' => new ChecksumNamer(),
            'slugify' => new SlugifyNamer(),
        ];
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
        if ($this->namers instanceof ContainerInterface) {
            return $this->namers->get($name);
        }

        return $this->namers[$name] ?? throw new \InvalidArgumentException(\sprintf('Namer "%s" is not registered.', $name));
    }
}
