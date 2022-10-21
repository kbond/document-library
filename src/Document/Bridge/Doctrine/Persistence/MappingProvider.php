<?php

namespace Zenstruck\Document\Bridge\Doctrine\Persistence;

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
     * @return array<string,array<string,array<string,mixed>>>
     */
    public function all(): array;
}
