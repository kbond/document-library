<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture;

use Zenstruck\Document\Library;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Service
{
    public function __construct(public Library $public, public Library $private, public LibraryRegistry $registry)
    {
    }
}
