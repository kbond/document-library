<?php

namespace Zenstruck\Document\Attribute;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class UploadedFile
{
    public function __construct(
        public ?string $path = null
    ) {
    }
}
