<?php

namespace Zenstruck\Document\Library\Bridge\Symfony;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types\DocumentJsonType;
use Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types\DocumentStringType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckDocumentLibraryBundle extends Bundle
{
    public function boot(): void
    {
        parent::boot();

        if (!\class_exists(Type::class)) {
            return;
        }

        foreach ([DocumentJsonType::class, DocumentStringType::class] as $type) {
            if (!Type::hasType($type::NAME)) {
                Type::addType($type::NAME, $type);
            }
        }
    }
}
