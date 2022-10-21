<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface MappingProvider
{
    /**
     * @param class-string $class
     *
     * @return array<string,array<string,mixed>>
     */
    public function get(string $class): array;

    /**
     * @return array<class-string,array<string,array<string,mixed>>>
     */
    public function all(): array;
}