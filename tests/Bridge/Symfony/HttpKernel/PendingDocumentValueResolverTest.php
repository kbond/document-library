<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\HttpKernel;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 */
class PendingDocumentValueResolverTest extends WebTestCase
{
    /**
     * @test
     */
    public function do_nothing_on_wrong_type(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            'no-injection',
            files: ['file' => self::uploadedFile()]
        );

        self::assertSame('0', $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function inject_on_typed_argument(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            'single-file',
        );

        self::assertSame('', $client->getResponse()->getContent());

        $client->request(
            'GET',
            'single-file',
            files: ['file' => self::uploadedFile()]
        );

        self::assertSame("content\n", $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function inject_on_typed_argument_with_path(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            'single-file-with-path',
            files: ['data' => ['file' => self::uploadedFile()]]
        );

        self::assertSame("content\n", $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function inject_array_on_argument_with_attribute(): void
    {

        $client = self::createClient();

        $client->request(
            'GET',
            'multiple-files'
        );

        self::assertSame('0', $client->getResponse()->getContent());

        $client->request(
            'GET',
            'multiple-files',
            files: ['files' => self::uploadedFile()]
        );

        self::assertSame('1', $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function inject_array_on_argument_with_attribute_and_path(): void
    {

        $client = self::createClient();

        $client->request(
            'GET',
            'multiple-files-with-path'
        );

        self::assertSame('0', $client->getResponse()->getContent());

        $client->request(
            'GET',
            'multiple-files-with-path',
            files: ['data' => ['files' => self::uploadedFile()]]
        );

        self::assertSame('1', $client->getResponse()->getContent());
    }

    private static function uploadedFile(): UploadedFile
    {
        return new UploadedFile(
            __DIR__.'/../Fixture/File/test.txt',
            'test.txt',
            test: true
        );
    }
}
