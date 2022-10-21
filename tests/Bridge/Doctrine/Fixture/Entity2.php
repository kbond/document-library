<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Entity2
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;
}
