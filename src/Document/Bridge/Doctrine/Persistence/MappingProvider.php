<?php

namespace Zenstruck\Document\Bridge\Doctrine\Persistence;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface MappingProvider
{
    /**
     * @param class-string $class
     */
    public function get(string $class): array;
}
