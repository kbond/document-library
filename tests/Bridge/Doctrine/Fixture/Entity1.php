<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;
use Zenstruck\Document\NullDocument;

#[ORM\Entity]
class Entity1
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(nullable: true, unique: true)]
    public ?string $name = null;

    #[ORM\Column(type: Document::class, nullable: true, options: [
        'library' => 'memory',
        'namer' => 'expression:prefix/{this.name|slug}-{checksum:7}{ext}',
    ])]
    public ?Document $document1 = null;

    #[Context(['library' => 'memory', 'metadata' => ['library', 'path', 'size']])]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $document2 = null;

    #[Mapping(
        library: 'memory',
        namer: 'expression:prefix/{this.name|slug}-{checksum:7}{ext}',
        metadata: ['checksum', 'extension'],
    )]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $document3 = null;

    #[Mapping(
        library: 'memory',
        metadata: true,
    )]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $document5 = null;

    #[Mapping(library: 'memory')]
    #[ORM\Column(type: 'document_string', nullable: true)]
    public ?Document $document6 = null;

    #[Mapping(
        library: 'memory',
        namer: 'expression:prefix/{this.name|slug}.txt',
    )]
    private Document $document4;

    public function __construct()
    {
        $this->document4 = new NullDocument();
    }

    public function document4(): ?Document
    {
        return $this->document4->exists() ? $this->document4 : null;
    }
}
