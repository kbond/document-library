<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Document\Attribute\UploadedFile;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 */
class SingleFileWithPathController extends AbstractController
{
    public function __invoke(
        #[UploadedFile('data[file]')]
        ?PendingDocument $file
    ): Response {
        return new Response();
    }
}
