<?php

namespace Zenstruck\Document\Bridge\Doctrine\Persistence;

use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ObjectDocumentLoader
{
    public function __construct(private LibraryRegistry $registry, private MappingConfigProvider $config)
    {
    }

    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T
     */
    public function load(object $object, ?string $property = null): object
    {
        if (!$config = $this->config->get($object::class)) {
            return $object;
        }

        (new ObjectReflector($object, $config))->load($this->registry, $property);

        return $object;
    }
}
