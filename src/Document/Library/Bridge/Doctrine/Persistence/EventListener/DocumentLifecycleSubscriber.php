<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs as ORMPreUpdateEventsArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\PreUpdateEventArgs;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\MappingProvider;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\ObjectReflector;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer;
use Zenstruck\Document\PendingDocument;
use Zenstruck\Document\SerializableDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DocumentLifecycleSubscriber
{
    /** @var callable[] */
    private array $pendingOperations = [];

    public function __construct(
        private LibraryRegistry $registry,
        private MappingProvider $mappingProvider,
        private Namer $namer,
    ) {
    }

    /**
     * @param LifecycleEventArgs<ObjectManager> $event
     */
    final public function postLoad(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if (!$mappings = $this->mappingProvider()->get($object::class)) {
            return;
        }

        // todo make properties that can be auto-loaded configurable in mapping
        foreach ((new ObjectReflector($object))->documents($mappings) as $property => $document) {
            $document->setLibrary($this->registry()->get($mappings[$property]->library));

            if ($document->isNamerRequired()) {
                $document->setNamer($this->namer(), self::namerContext($mappings[$property], $object));
            }
        }
    }

    /**
     * @param LifecycleEventArgs<ObjectManager> $event
     */
    final public function postRemove(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if (!$mappings = $this->mappingProvider()->get($object::class)) {
            return;
        }

        foreach ($mappings as $property => $mapping) {
            // todo make properties that can be auto-removed configurable in mapping
            $ref ??= new ObjectReflector($object);
            $document = $ref->get($property);

            if ($document instanceof Document && $document->exists()) {
                $this->registry()->get($mapping->library)->delete($document->path());
            }
        }
    }

    /**
     * @param LifecycleEventArgs<ObjectManager> $event
     */
    final public function prePersist(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if (!$mappings = $this->mappingProvider()->get($object::class)) {
            return;
        }

        foreach ($mappings as $property => $mapping) {
            $ref ??= new ObjectReflector($object);

            if ($mapping->virtual) {
                // set virtual document
                $ref->set($property, (new LazyDocument([]))
                    ->setLibrary($this->registry()->get($mapping->library))
                    ->setNamer($this->namer(), self::namerContext($mapping, $object))
                );

                continue;
            }

            $document = $ref->get($property);

            if (!$document instanceof Document) {
                continue;
            }

            if ($document instanceof PendingDocument) {
                $document = $document->withPath(
                    $this->namer()->generateName($document, self::namerContext($mapping, $object))
                );

                $this->pendingOperations[] = function() use ($document, $mapping) {
                    $this->registry()->get($mapping->library)->store($document->path(), $document);
                };

                $ref->set($property, $document);
            }

            if (!$document instanceof SerializableDocument && $mapping->metadata) {
                // save with metadata
                $ref->set($property, new SerializableDocument($document, $mapping->metadata));
            }
        }
    }

    /**
     * @param PreUpdateEventArgs<ObjectManager>|ORMPreUpdateEventsArgs $event
     */
    final public function preUpdate(PreUpdateEventArgs|ORMPreUpdateEventsArgs $event): void
    {
        $object = $event->getObject();

        if (!$mappings = $this->mappingProvider()->get($object::class)) {
            return;
        }

        foreach ($mappings as $property => $mapping) {
            if (!$event->hasChangedField($property)) {
                continue;
            }

            $old = $event->getOldValue($property);
            $new = $event->getNewValue($property);

            if ($new instanceof PendingDocument) {
                $new = $new->withPath(
                    $this->namer()->generateName($new, self::namerContext($mapping, $object))
                );

                $this->pendingOperations[] = function() use ($new, $mapping) {
                    $this->registry()->get($mapping->library)->store($new->path(), $new);
                };

                $event->setNewValue($property, $new);
            }

            if ($new instanceof Document && $old instanceof Document && $new->path() !== $old->path()) {
                // todo make configurable via mapping
                // document was changed, delete old from library
                $this->pendingOperations[] = fn() => $this->registry()->get($mapping->library)->delete($old->path());
            }

            if ($new instanceof Document && !$new instanceof SerializableDocument && $mapping->metadata) {
                // save with metadata
                $event->setNewValue($property, new SerializableDocument($new, $mapping->metadata));
            }

            if ($old instanceof Document && null === $new) {
                // todo make configurable via mapping
                // document was removed, delete from library
                $this->pendingOperations[] = fn() => $this->registry()->get($mapping->library)->delete($old->path());
            }
        }
    }

    final public function postFlush(): void
    {
        foreach ($this->pendingOperations as $operation) {
            $operation();
        }

        $this->pendingOperations = [];
    }

    protected function registry(): LibraryRegistry
    {
        return $this->registry;
    }

    protected function mappingProvider(): MappingProvider
    {
        return $this->mappingProvider;
    }

    protected function namer(): Namer
    {
        return $this->namer;
    }

    private static function namerContext(Mapping $mapping, object $object): array
    {
        return \array_merge($mapping->toArray(), ['this' => $object]);
    }
}