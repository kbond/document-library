<?php
declare(strict_types=1);


namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Document\Attribute\UploadedFile;

class MultipleFilesController extends AbstractController
{
    public function __invoke(
        #[UploadedFile('data[files]')]
        array $file
    ): Response {
        return new Response();
    }
}