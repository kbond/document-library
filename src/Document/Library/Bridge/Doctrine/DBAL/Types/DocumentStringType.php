<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentStringType extends StringType
{
    public const NAME = 'document_string';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof Document ? $value->dsn() : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Document
    {
        return \is_string($value) ? new LazyDocument($value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
