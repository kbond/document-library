<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;

#[ORM\Entity]
class Entity1
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(type: Document::class, options: ['library' => 'public'])]
    public Document $document1;
}
