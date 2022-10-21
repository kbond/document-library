<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence;

use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ObjectReflector
{
    private \ReflectionObject $ref;

    /** @var array<string,\ReflectionProperty> */
    private array $properties = [];

    public function __construct(private object $object, private array $config)
    {
        $this->ref = new \ReflectionObject($object);
    }

    public function load(LibraryRegistry $registry, string ...$properties): void
    {
        $properties = $properties ?: \array_keys($this->config);

        foreach ($properties as $property) {
            $document = $this->get($property);

            if (!$document instanceof LazyDocument) {
                continue;
            }

            $document->setLibrary($registry->get($this->config[$property]['library']));
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
