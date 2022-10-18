<?php

namespace Zenstruck\Document\Namer;

use Zenstruck\Document;
use Zenstruck\Document\Namer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallbackNamer implements Namer
{
    /** @var callable(Document,array<string,mixed>):string */
    private $callback;

    /**
     * @param callable(Document,array<string,mixed>):string $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function generateName(Document $document, array $context = []): string
    {
        return ($this->callback)($document, $context);
    }
}
