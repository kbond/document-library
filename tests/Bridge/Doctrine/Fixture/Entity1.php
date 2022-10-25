<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
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

    #[ORM\Column(type: Document::class, nullable: true, options: [
        'library' => 'memory',
        'expression' => 'prefix/{this.name|slug}-{checksum:7}{ext}',
    ])]
    public ?Document $document1 = null;

    #[Context(['library' => 'memory', 'metadata' => ['path', 'size']])]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $document2 = null;
}
