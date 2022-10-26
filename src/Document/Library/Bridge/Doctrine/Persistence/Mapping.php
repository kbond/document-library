<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence;

use Symfony\Component\Serializer\Annotation\Context;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Mapping
{
    /** @internal */
    public bool $virtual = false;

    public function __construct(
        public string $library,
        public ?string $namer = null,
        public ?string $expression = null,
        public array $metadata = [],
        public bool $autoload = true,
        public bool $deleteOnRemove = true,
        public bool $deleteOnChange = true,
        public array $extra = [],
    ) {
    }

    /**
     * @internal
     */
    public static function fromProperty(\ReflectionProperty $property, array $mapping = []): self
    {
        if (\class_exists(Context::class) && $attribute = $property->getAttributes(Context::class)[0] ?? null) {
            $mapping = \array_merge($mapping, $attribute->newInstance()->getContext());
        }

        if ($attribute = $property->getAttributes(self::class)[0] ?? null) {
            $mapping = \array_merge($mapping, $attribute->newInstance()->toArray());
        }

        return new self(
            $mapping['library'] ?? throw new \LogicException(\sprintf('A library is not configured for %s::$%s.', $property->class, $property->name)),
            $mapping['namer'] ?? null,
            $mapping['expression'] ?? null,
            $mapping['metadata'] ?? [],
            $mapping['autoload'] ?? true,
            $mapping['deleteOnRemove'] ?? true,
            $mapping['deleteOnChange'] ?? true,
            \array_diff_key($mapping, \array_flip([
                'library', 'namer', 'expression', 'metadata', 'autoload', 'deleteOnRemove', 'deleteOnChange',
            ])),
        );
    }

    /**
     * @internal
     */
    public function toArray(): array
    {
        return \array_filter(\array_merge($this->extra, [
            'library' => $this->library,
            'namer' => $this->namer,
            'expression' => $this->expression,
            'metadata' => $this->metadata,
        ]));
    }
}
