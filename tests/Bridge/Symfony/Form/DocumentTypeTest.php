<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Form;

use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Symfony\Form\DocumentType;
use Zenstruck\Document\Library\Tests\TestCase;
use Zenstruck\Document\Namer\ChecksumNamer;
use Zenstruck\Document\Namer\MultiNamer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentTypeTest extends TypeTestCase
{
    /**
     * @test
     */
    public function library_is_required(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->factory->create(DocumentType::class);
    }

    /**
     * @test
     */
    public function library_instance_is_required_if_registry_not_available(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->factory->create(DocumentType::class, options: ['library' => 'memory', 'namer' => new MultiNamer()]);
    }

    /**
     * @test
     */
    public function set_data(): void
    {
        $library = TestCase::inMemoryLibrary();
        $form = $this->factory->create(DocumentType::class, options: ['library' => $library, 'namer' => new MultiNamer()]);

        $data = $library->store('some/file.txt', 'content');

        $form->setData($data);

        $this->assertSame($data, $form->getData());
    }

    /**
     * @test
     */
    public function set_data_multiple(): void
    {
        $library = TestCase::inMemoryLibrary();
        $form = $this->factory->create(DocumentType::class, options: [
            'library' => $library,
            'namer' => new MultiNamer(),
            'multiple' => true,
        ]);

        $data = [
            $library->store('some/file1.txt', 'content'),
            $library->store('some/file2.txt', 'content'),
        ];

        $form->setData($data);

        $this->assertSame($data, $form->getData());
    }

    /**
     * @test
     */
    public function submit(): void
    {
        $file = __DIR__.'/Fixture/file1.txt';
        $library = TestCase::inMemoryLibrary();
        $form = $this->factory->createBuilder(DocumentType::class, options: [
                'library' => $library,
                'namer' => new ChecksumNamer(),
            ])
            ->setRequestHandler(new HttpFoundationRequestHandler())
            ->getForm()
        ;
        $data = new UploadedFile($file, 'file1.txt', test: true);

        $form->submit($data);

        $this->assertInstanceOf(Document::class, $form->getData());
        $this->assertStringEqualsFile($file, $form->getData()->contents());
        $this->assertTrue($library->has(\md5_file($file).'.txt'));
    }

    /**
     * @test
     */
    public function submit_multiple(): void
    {
        $files = [__DIR__.'/Fixture/file1.txt', __DIR__.'/Fixture/file2.txt'];
        $library = TestCase::inMemoryLibrary();
        $form = $this->factory->createBuilder(DocumentType::class, options: [
                'multiple' => true,
                'library' => $library,
                'namer' => new ChecksumNamer(),
            ])
            ->setRequestHandler(new HttpFoundationRequestHandler())
            ->getForm()
        ;
        $data = [
            new UploadedFile($files[0], 'file1.txt', test: true),
            new UploadedFile($files[1], 'file2.txt', test: true),
        ];

        $form->submit($data);

        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertInstanceOf(Document::class, $form->getData()[0]);
        $this->assertInstanceOf(Document::class, $form->getData()[1]);
        $this->assertStringEqualsFile($files[0], $form->getData()[0]->contents());
        $this->assertStringEqualsFile($files[1], $form->getData()[1]->contents());
        $this->assertTrue($library->has(\md5_file($files[0]).'.txt'));
        $this->assertTrue($library->has(\md5_file($files[1]).'.txt'));
    }
}
