<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;

#[ORM\Entity]
class Entity1
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(nullable: true)]
    public ?string $name = null;

    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $document1 = null;
}
