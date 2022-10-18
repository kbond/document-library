<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface LazyDocument extends Document
{
    public function setLibrary(Library $library): static;
}
