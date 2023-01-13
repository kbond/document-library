<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence;

use Symfony\Component\Serializer\Annotation\Context;
use Zenstruck\Document\SerializableDocument;

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
        public array|bool $metadata = false,
        public bool $autoload = true,
        public bool $deleteOnRemove = true,
        public bool $deleteOnChange = true,
        private bool $nameOnLoad = false,
        public array $extra = [],
    ) {
        if (\is_array($this->metadata) && !$this->metadata) {
            throw new \InvalidArgumentException('$metadata cannot be empty.');
        }

        if (\is_array($this->metadata) && !\in_array('path', $this->metadata, true)) {
            $this->nameOnLoad = true;
        }
    }

    /**
     * @internal
     */
    public function nameOnLoad(): bool
    {
        return $this->nameOnLoad || $this->virtual;
    }

    /**
     * @internal
     */
    public function serializationMode(): string
    {
        return match (true) {
            (false !== $this->metadata) => SerializableDocument::SERIALIZE_AS_ARRAY,
            default => SerializableDocument::SERIALIZE_AS_STRING
        };
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
            $mapping['metadata'] ?? false,
            $mapping['autoload'] ?? true,
            $mapping['deleteOnRemove'] ?? true,
            $mapping['deleteOnChange'] ?? true,
            $mapping['nameOnLoad'] ?? false,
            \array_diff_key($mapping, \array_flip([
                'library', 'namer', 'metadata', 'onlyPath', 'autoload', 'deleteOnRemove', 'deleteOnChange', 'nameOnLoad',
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
            'metadata' => $this->metadata,
        ]));
    }
}
