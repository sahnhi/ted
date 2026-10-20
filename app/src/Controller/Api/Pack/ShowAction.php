<?php

namespace App\Controller\Api\Pack;

use App\ViewRepository\PackViewRepositoryInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ShowAction
{
    public function __construct(
        private readonly PackViewRepositoryInterface $packViewRepository,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $context = new Context();
            $context->setGroups(['pack.show']);

            $view = View::create($this->packViewRepository->findOneByChannelAndCode($request->attributes->get('channelCode'), $request->attributes->get('code')), Response::HTTP_OK);
            $view->setContext($context);

            return $this->viewHandler->handle($view);
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
