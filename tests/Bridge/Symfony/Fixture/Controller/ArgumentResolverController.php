<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Document\Attribute\UploadedFile;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 */
class ArgumentResolverController extends AbstractController
{
    public function multipleFiles(
        #[UploadedFile('data[files]')]
        array $files
    ): Response {
        return new Response((string) \count($files));
    }

    public function noInjection(array $params): Response
    {
        return new Response((string) \count($params));
    }

    public function singleFile(?PendingDocument $file): Response
    {
        return new Response($file?->contents() ?? '');
    }

    public function singleFileWithPath(
        #[UploadedFile('data[file]')]
        ?PendingDocument $file
    ): Response {
        return new Response($file?->contents() ?? '');
    }
}
