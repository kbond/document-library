<?php

namespace Zenstruck\Document\Namer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Expression implements \Stringable
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return "expression:{$this->value}";
    }
}
