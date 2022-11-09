<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Form;

use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Document\Library\Bridge\Symfony\Form\PendingDocumentType;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PendingDocumentTypeTest extends TypeTestCase
{
    /**
     * @test
     */
    public function set_data(): void
    {
        $form = $this->factory->create(PendingDocumentType::class);

        $data = new PendingDocument(__DIR__.'/Fixture/file1.txt');

        $form->setData($data);

        $this->assertSame($data, $form->getData());
    }

    /**
     * @test
     */
    public function set_data_multiple(): void
    {
        $form = $this->factory->create(PendingDocumentType::class, options: ['multiple' => true]);

        $data = [
            new PendingDocument(__DIR__.'/Fixture/file1.txt'),
            new PendingDocument(__DIR__.'/Fixture/file2.txt'),
        ];

        $form->setData($data);

        $this->assertSame($data, $form->getData());
    }

    /**
     * @test
     */
    public function submit(): void
    {
        $form = $this->factory->createBuilder(PendingDocumentType::class)
            ->setRequestHandler(new HttpFoundationRequestHandler())
            ->getForm()
        ;
        $data = new UploadedFile(__DIR__.'/Fixture/file1.txt', 'file1.txt', test: true);

        $form->submit($data);

        $this->assertInstanceOf(PendingDocument::class, $form->getData());
        $this->assertSame($data->getClientOriginalName(), $form->getData()->name());
    }

    /**
     * @test
     */
    public function submit_multiple(): void
    {
        $form = $this->factory->createBuilder(PendingDocumentType::class, options: ['multiple' => true])
            ->setRequestHandler(new HttpFoundationRequestHandler())
            ->getForm()
        ;
        $data = [
            new UploadedFile(__DIR__.'/Fixture/file1.txt', 'file1.txt', test: true),
            new UploadedFile(__DIR__.'/Fixture/file2.txt', 'file2.txt', test: true),
        ];

        $form->submit($data);

        $this->assertIsArray($form->getData());
        $this->assertCount(2, $form->getData());
        $this->assertInstanceOf(PendingDocument::class, $form->getData()[0]);
        $this->assertInstanceOf(PendingDocument::class, $form->getData()[1]);
        $this->assertSame($data[0]->getClientOriginalName(), $form->getData()[0]->name());
        $this->assertSame($data[1]->getClientOriginalName(), $form->getData()[1]->name());
    }
}
