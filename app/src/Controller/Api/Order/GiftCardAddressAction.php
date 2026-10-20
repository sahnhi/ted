<?php

namespace App\Controller\Api\Order;

use App\Business\Command\GiftCardAddressOrder;
use App\Model\GiftCardAddress;
use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GiftCardAddressAction
{
    public function __construct(
        private readonly CommandBus $bus,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->bus->handle(new GiftCardAddressOrder(
            $request->attributes->get('token'),
            GiftCardAddress::createFromArray($request->request->get('giftCardAddress'))
        ));

        try {
            return $this->viewHandler->handle(
                View::create($this->cartQuery->getOneByToken($request->attributes->get('token')), Response::HTTP_OK)
            );
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
