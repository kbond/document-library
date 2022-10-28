<?php

namespace Zenstruck\Document\Library\Tests;

use Zenstruck\Document;
use Zenstruck\Document\Image;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class DocumentTest extends TestCase
{
    /**
     * @test
     */
    public function access_document_path_info(): void
    {
        $document = $this->document('some/file.png', new \SplFileInfo(self::FIXTURE_DIR.'/symfony.png'));

        $this->assertSame('some/file.png', $document->path());
        $this->assertSame('file.png', $document->name());
        $this->assertSame('png', $document->extension());
        $this->assertSame('file', $document->nameWithoutExtension());
    }

    /**
     * @test
     */
    public function access_document_data(): void
    {
        $file = self::FIXTURE_DIR.'/symfony.png';
        $document = $this->document('some/file.png', new \SplFileInfo($file));

        $this->assertIsInt($document->lastModified());
        $this->assertSame(\filesize($file), $document->size());
        $this->assertSame(\md5_file($file), $document->checksum());
        $this->assertSame(\sha1_file($file), $document->checksum('sha1'));
        $this->assertStringEqualsFile($file, $document->contents());
        $this->assertStringEqualsFile($file, \stream_get_contents($document->read()));
        $this->assertTrue($document->exists());
        $this->assertSame('image/png', $document->mimeType());
    }

    /**
     * @test
     */
    public function refresh_resets_any_cached_metadata(): void
    {
        $file = self::FIXTURE_DIR.'/symfony.png';
        $document = $this->document('some/file.png', new \SplFileInfo($file));

        $this->assertIsInt($document->lastModified());
        $this->assertSame(\filesize($file), $document->size());
        $this->assertSame(\md5_file($file), $document->checksum());
        $this->assertTrue($document->exists());
        $this->assertSame('image/png', $document->mimeType());

        $this->modifyDocument('some/file.png', 'new content');

        $this->assertIsInt($document->lastModified());
        $this->assertSame(\filesize($file), $document->size());
        $this->assertSame(\md5_file($file), $document->checksum());
        $this->assertTrue($document->exists());
        $this->assertSame('image/png', $document->mimeType());

        $document->refresh();

        $this->assertIsInt($document->lastModified());
        $this->assertSame(11, $document->size());
        $this->assertSame('96c15c2bb2921193bf290df8cd85e2ba', $document->checksum());
        $this->assertTrue($document->exists());
        // $this->assertSame('text/plain', $document->mimeType());
        $this->assertSame('image/png', $document->mimeType());
    }

    /**
     * @test
     */
    public function can_get_temp_file(): void
    {
        $file = self::FIXTURE_DIR.'/symfony.png';
        $document = $this->document('some/file.png', new \SplFileInfo($file));

        $this->assertFileEquals($file, $document->tempFile());
    }

    /**
     * @test
     */
    public function refresh_is_mutable(): void
    {
        $document = $this->document('some/file.png', new \SplFileInfo(self::FIXTURE_DIR.'/symfony.png'));

        $this->assertSame($document, $document->refresh());
    }

    /**
     * @test
     */
    public function can_cast_to_image(): void
    {
        $file = self::FIXTURE_DIR.'/symfony.png';
        $document = $this->nonImageDocument('some/image.png', new \SplFileInfo($file));

        if ($document instanceof Image) {
            $this->fail('Document is an image.');
        }

        $image = $document->asImage();

        $this->assertSame($document->path(), $image->path());
        $this->assertSame($document->name(), $image->name());
        $this->assertSame($document->nameWithoutExtension(), $image->nameWithoutExtension());
        $this->assertSame($document->extension(), $image->extension());
        $this->assertSame($document->lastModified(), $image->lastModified());
        $this->assertSame($document->size(), $image->size());
        $this->assertSame($document->checksum(), $image->checksum());
        $this->assertSame($document->checksum('sha1'), $image->checksum('sha1'));
        $this->assertSame($document->contents(), $image->contents());
        $this->assertSame(\stream_get_contents($document->read()), \stream_get_contents($image->read()));
        $this->assertSame($document->exists(), $image->exists());
        $this->assertSame($document->mimeType(), $image->mimeType());
    }

    /**
     * @test
     */
    public function cannot_cast_to_image_if_not_image(): void
    {
        $document = $this->nonImageDocument('some/file.txt', new \SplFileInfo(__FILE__));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"some/file.txt" is not an image.');

        $document->asImage();
    }

    protected function nonImageDocument(string $path, \SplFileInfo $file): Document
    {
        return $this->document($path, $file);
    }

    abstract protected function document(string $path, \SplFileInfo $file): Document;

    abstract protected function modifyDocument(string $path, string $content): void;
}
