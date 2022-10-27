<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Doctrine\DBAL\Type;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentStringTypeTest extends DocumentTypeTest
{
    protected function documentProperty(): string
    {
        return 'document6';
    }
}
