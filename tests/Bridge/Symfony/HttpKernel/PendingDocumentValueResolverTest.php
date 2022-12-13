<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\HttpKernel;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Zenstruck\Document\Library\Bridge\Symfony\HttpKernel\PendingDocumentValueResolver;
use Zenstruck\Document\Library\Bridge\Symfony\HttpKernel\RequestFilesExtractor;
use Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller\MultipleFilesController;
use Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller\NoInjectionController;
use Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller\SingleFileController;
use Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller\SingleFileWithPathController;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 */
class PendingDocumentValueResolverTest extends TestCase
{
    /**
     * @test
     */
    public function do_nothing_on_wrong_type(): void
    {
        $request = Request::create('');
        $arguments = self::metadataFactory()
            ->createArgumentMetadata(new NoInjectionController());
        $resolver = self::resolver();

        self::assertFalse($resolver->supports($request, $arguments[0]));
        self::assertSame([], $resolver->resolve($request, $arguments[0]));
    }

    /**
     * @test
     */
    public function inject_on_typed_argument(): void
    {
        $request = Request::create('');
        $arguments = self::metadataFactory()
            ->createArgumentMetadata(new SingleFileController());
        $resolver = self::resolver();

        $resolve = $resolver->resolve($request, $arguments[0]);

        self::assertTrue($resolver->supports($request, $arguments[0]));
        self::assertSame([null], $resolve);
    }

    /**
     * @test
     */
    public function inject_on_typed_argument_with_path(): void
    {
        $request = Request::create('');
        $request->files->set('data', ['file' => self::uploadedFile()]);
        $arguments = self::metadataFactory()
            ->createArgumentMetadata(new SingleFileWithPathController());
        $resolver = self::resolver();

        $resolve = $resolver->resolve($request, $arguments[0]);

        self::assertIsArray($resolve);
        self::assertCount(1, $resolve);
        self::assertInstanceOf(PendingDocument::class, $resolve[0]);
    }

    /**
     * @test
     */
    public function inject_array_on_argument_with_attribute(): void
    {
        $request = Request::create('');
        $request->files->set('data', ['files' => [self::uploadedFile()]]);
        $arguments = self::metadataFactory()
            ->createArgumentMetadata(new MultipleFilesController());
        $resolver = self::resolver();

        self::assertTrue($resolver->supports($request, $arguments[0]));

        $resolve = $resolver->resolve($request, $arguments[0]);

        self::assertIsArray($resolve);
        self::assertCount(1, $resolve);
        self::assertIsArray($resolve[0]);
        self::assertInstanceOf(PendingDocument::class, $resolve[0][0]);
    }

    private static function metadataFactory(): ArgumentMetadataFactory
    {
        return new ArgumentMetadataFactory();
    }

    private static function uploadedFile(): UploadedFile
    {
        return new UploadedFile(
            __DIR__.'/../Fixture/File/test.txt',
            'test.txt',
            test: true
        );
    }

    private static function resolver(): PendingDocumentValueResolver
    {
        return new PendingDocumentValueResolver(self::extractor());
    }

    private static function extractor(): RequestFilesExtractor
    {
        return new RequestFilesExtractor(
            new PropertyAccessor(
                PropertyAccessor::DISALLOW_MAGIC_METHODS,
                PropertyAccessor::THROW_ON_INVALID_PROPERTY_PATH
            )
        );
    }
}
