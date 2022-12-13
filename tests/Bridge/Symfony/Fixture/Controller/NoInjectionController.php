<?php
declare(strict_types=1);


namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class NoInjectionController extends AbstractController
{
    public function __invoke(array $params): Response
    {
        return new Response();
    }
}
