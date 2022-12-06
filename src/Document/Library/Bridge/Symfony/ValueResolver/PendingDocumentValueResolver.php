<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\ValueResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 *
 * @internal
 */
class PendingDocumentValueResolver implements ArgumentValueResolverInterface, ValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        @trigger_deprecation('symfony/http-kernel', '6.2', 'The "%s()" method is deprecated, use "resolve()" instead.', __METHOD__);

        return $argument->getType() === PendingDocument::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PendingDocument::class) {
            return [];
        }

        if (!$request->files->has($argument->getName())) {
            return [null];
        }

        return [new PendingDocument($request->files->get($argument->getName()))];
    }
}

