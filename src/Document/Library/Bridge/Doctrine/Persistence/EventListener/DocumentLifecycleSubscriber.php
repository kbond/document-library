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

    /** @var callable[] */
    private array $onFailureOperations = [];

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

        if (!$mappings = \array_filter($this->mappingProvider()->get($object::class), static fn(Mapping $m) => $m->autoload)) {
            return;
        }

        foreach ((new ObjectReflector($object))->documents($mappings) as $property => $document) {
            $document->setLibrary($this->registry());

            if ($mappings[$property]->nameOnLoad()) {
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

        if (!$mappings = \array_filter($this->mappingProvider()->get($object::class), static fn(Mapping $m) => $m->deleteOnRemove)) {
            return;
        }

        foreach ($mappings as $property => $mapping) {
            $ref ??= new ObjectReflector($object);
            $document = $ref->get($property);

            if ($document instanceof Document && $document->exists()) {
                $this->registry()->getForDocument($document)->delete($document->path());
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
                $ref->set($property, (new LazyDocument(['library' => $mapping->library]))
                    ->setLibrary($this->registry())
                    ->setNamer($this->namer(), self::namerContext($mapping, $object))
                );

                continue;
            }

            $document = $ref->get($property);

            if (!$document instanceof Document) {
                continue;
            }

            if ($document instanceof PendingDocument) {
                $document = $this->registry()->get($mapping->library)->store(
                    $path = $this->namer()->generateName($document, self::namerContext($mapping, $object)),
                    $document
                );
                $this->onFailureOperations[] = fn() => $this->registry()->getForDocument($document)->delete($path);

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
                $new = $this->registry()->get($mapping->library)->store(
                    $path = $this->namer()->generateName($new, self::namerContext($mapping, $object)),
                    $new
                );
                $this->onFailureOperations[] = fn() => $this->registry()->get($mapping->library)->delete($path);

                $event->setNewValue($property, $new);
            }

            if ($mapping->deleteOnChange && $new instanceof Document && $old instanceof Document && $new->dsn() !== $old->dsn()) {
                // document was changed, delete old from library
                $this->pendingOperations[] = fn() => $this->registry()->getForDocument($old)->delete($old->path());
            }

            if ($new instanceof Document && !$new instanceof SerializableDocument && $mapping->metadata) {
                // save with metadata
                $event->setNewValue($property, new SerializableDocument($new, $mapping->metadata));
            }

            if ($mapping->deleteOnChange && $old instanceof Document && null === $new) {
                // document was removed, delete from library
                $this->pendingOperations[] = fn() => $this->registry()->getForDocument($old)->delete($old->path());
            }
        }
    }

    final public function postFlush(): void
    {
        foreach ($this->pendingOperations as $operation) {
            $operation();
        }

        $this->pendingOperations = $this->onFailureOperations = [];
    }

    final public function onClear(): void
    {
        foreach ($this->onFailureOperations as $operation) {
            $operation();
        }

        $this->pendingOperations = $this->onFailureOperations = [];
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
