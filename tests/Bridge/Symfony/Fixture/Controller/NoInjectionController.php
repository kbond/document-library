<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Fixture\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 */
class NoInjectionController extends AbstractController
{
    public function __invoke(array $params): Response
    {
        return new Response();
    }
}
