<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Namer
{
    public function generateName(Document $document, array $context = []): string;
}
