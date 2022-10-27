<?php

namespace Zenstruck\Document\Namer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Expression
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return "expression:{$this->value}";
    }
}
