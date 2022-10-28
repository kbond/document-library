<?php

namespace Zenstruck\Document\Library\Tests\Image;

use Zenstruck\Document;
use Zenstruck\Document\Image;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FlysystemImageTest extends ImageTest
{
    protected function nonImageDocument(string $path, \SplFileInfo $file): Document
    {
        return self::$library->store($path, $file);
    }

    protected function document(string $path, \SplFileInfo $file): Image
    {
        return $this->nonImageDocument($path, $file)->asImage();
    }

    protected function modifyDocument(string $path, string $content): void
    {
        self::$library->store($path, $content);
    }
}
