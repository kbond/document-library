<?php
declare(strict_types=1);


namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Document\Attribute\UploadedFile;
use Zenstruck\Document\PendingDocument;

class SingleFileWithPathController extends AbstractController
{
    public function __invoke(
        #[UploadedFile('data[file]')]
        ?PendingDocument $file
    ): Response {
        return new Response();
    }
}
