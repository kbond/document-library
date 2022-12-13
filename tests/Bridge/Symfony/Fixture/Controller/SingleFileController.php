<?php
declare(strict_types=1);


namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Document\PendingDocument;

class SingleFileController extends AbstractController
{
    public function __invoke(?PendingDocument $file): Response
    {
        return new Response();
    }
}
