<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence;

use Zenstruck\Document;
use Zenstruck\Document\Attribute\Mapping;
use Zenstruck\Document\LazyDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ObjectReflector
{
    private \ReflectionObject $ref;

    /** @var array<string,\ReflectionProperty> */
    private array $properties = [];

    /**
     * @param array<string,Mapping> $config
     */
    public function __construct(private object $object, private array $config)
    {
        $this->ref = new \ReflectionObject($object);
    }

    /**
     * @return LazyDocument[]
     */
    public function documents(string ...$properties): iterable
    {
        $properties = $properties ?: \array_keys($this->config);

        foreach ($properties as $property) {
            if ($this->config[$property]->extra['_virtual'] ?? false) {
                $this->set($property, $document = new LazyDocument([]));

                yield $property => $document;

                continue;
            }

            $document = $this->get($property);

            if (!$document instanceof LazyDocument) {
                continue;
            }

            yield $property => $document;
        }
    }

    public function get(string $property): ?Document
    {
        $ref = $this->property($property);

        if (!$ref->isInitialized($this->object)) {
            return null;
        }

        $document = $ref->getValue($this->object);

        return $document instanceof Document ? $document : null;
    }

    public function set(string $property, Document $document): void
    {
        $this->property($property)->setValue($this->object, $document);
    }

    private function property(string $name): \ReflectionProperty
    {
        // todo embedded

        if (\array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        $this->properties[$name] = $this->ref->getProperty($name);
        $this->properties[$name]->setAccessible(true);

        return $this->properties[$name];
    }
}
