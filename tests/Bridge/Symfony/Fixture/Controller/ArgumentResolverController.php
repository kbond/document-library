<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zenstruck\Document\Attribute\UploadedFile;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 */
class ArgumentResolverController
{
    #[Route('/multiple-files', name: 'multiple-files')]
    public function multipleFiles(
        #[UploadedFile('data[files]')]
        array $files
    ): Response {
        return new Response((string) \count($files));
    }

    #[Route('/no-injection', name: 'no-injection')]
    public function noInjection(array $file = []): Response
    {
        return new Response((string) \count($file));
    }

    #[Route('/single-file', name: 'single-file')]
    public function singleFile(?PendingDocument $file): Response
    {
        return new Response($file?->contents() ?? '');
    }

    #[Route('/single-file-with-path', name: 'single-file-with-path')]
    public function singleFileWithPath(
        #[UploadedFile('data[file]')]
        ?PendingDocument $file
    ): Response {
        return new Response($file?->contents() ?? '');
    }
}
