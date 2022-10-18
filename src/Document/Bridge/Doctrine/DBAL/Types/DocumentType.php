<?php

namespace Zenstruck\Document\Bridge\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\PendingDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class DocumentType extends StringType
{
    public const NAME = Document::class;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof PendingDocument) {
            throw new ConversionException(); // todo
        }

        return $value instanceof Document ? $value->path() : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Document
    {
        return \is_string($value) ? new LazyDocument($value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
