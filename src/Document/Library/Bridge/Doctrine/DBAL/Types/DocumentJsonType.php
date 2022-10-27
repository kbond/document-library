<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\SerializableDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentJsonType extends JsonType
{
    public const NAME = Document::class;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Document) {
            throw ConversionException::conversionFailedInvalidType($value, Document::class, [Document::class, 'null']);
        }

        return parent::convertToDatabaseValue(
            $value instanceof SerializableDocument ? $value->serialize() : $value->path(),
            $platform
        );
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Document
    {
        if (!$value) {
            return null;
        }

        if (!\is_string($value) && !\is_array($value)) {
            throw ConversionException::conversionFailedFormat($value, Document::class, 'string|array|null');
        }

        return new LazyDocument(parent::convertToPHPValue($value, $platform));
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
