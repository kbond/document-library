<?php

namespace Zenstruck\Document\Library\Tests\Image;

use Zenstruck\Document\Image;
use Zenstruck\Document\Library\Tests\DocumentTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ImageTest extends DocumentTest
{
    /**
     * @test
     */
    public function can_get_image_metadata(): void
    {
        foreach (['jpg', 'gif', 'png'] as $ext) {
            $image = $this->document("some/image.{$ext}", new \SplFileInfo(self::FIXTURE_DIR."/symfony.{$ext}"));

            $this->assertSame(563, $image->width());
            $this->assertSame(678, $image->height());
            $this->assertSame(563 * 678, $image->pixels());
            $this->assertSame(0.83, \round($image->aspectRatio(), 2));
            $this->assertTrue($image->isPortrait());
            $this->assertFalse($image->isLandscape());
            $this->assertFalse($image->isSquare());
            $this->assertSame([], $image->iptc());
        }

        $image = $this->document('some/image.svg', new \SplFileInfo(self::FIXTURE_DIR.'/symfony.svg'));

        $this->assertSame(202, $image->width());
        $this->assertSame(224, $image->height());
        $this->assertSame(0.90, \round($image->aspectRatio(), 2));
        $this->assertTrue($image->isPortrait());
        $this->assertFalse($image->isLandscape());
        $this->assertFalse($image->isSquare());
        $this->assertSame([], $image->iptc());

        $image = $this->document('some/image.jpg', new \SplFileInfo(self::FIXTURE_DIR.'/metadata.jpg'));
        $this->assertSame(16, $image->exif()['computed.Height']);
        $this->assertSame('Lorem Ipsum', $image->iptc()['DocumentTitle']);
        $this->assertTrue($image->isSquare());
        $this->assertFalse($image->isLandscape());
        $this->assertFalse($image->isPortrait());
    }

    /**
     * @test
     */
    public function as_image_returns_self(): void
    {
        $image = $this->document('some/image.png', new \SplFileInfo(self::FIXTURE_DIR.'/symfony.png'));

        $this->assertSame($image, $image->asImage());
    }

    abstract protected function document(string $path, \SplFileInfo $file): Image;
}
