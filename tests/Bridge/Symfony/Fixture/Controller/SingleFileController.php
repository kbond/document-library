<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 */
class SingleFileController extends AbstractController
{
    public function __invoke(?PendingDocument $file): Response
    {
        return new Response();
    }
}
