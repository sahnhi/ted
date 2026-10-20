<?php

namespace App\Controller\Api\Order;

use App\Business\Command\ChoosePayment;
use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ChoosePaymentAction
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->commandBus->handle(new ChoosePayment(
            $request->attributes->get('token'),
            $request->request->get('code')
        ));

        return $this->viewHandler->handle(
            View::create($this->cartQuery->getOneByToken($request->attributes->get('token')), Response::HTTP_OK)
        );
    }
}
