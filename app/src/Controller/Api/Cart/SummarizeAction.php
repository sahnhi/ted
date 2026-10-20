<?php

namespace App\Controller\Api\Cart;

use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SummarizeAction
{
    public const ROUTE_TOKEN = 'shop_api_cart_summary';

    public function __construct(
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $route = $request->get('_route');

        try {
            return $this->viewHandler->handle(
                View::create(
                    self::ROUTE_TOKEN === $route ?
                        $this->cartQuery->getOneByToken($request->attributes->get('token'), withVariants: true) :
                        $this->cartQuery->getOneByEmailAndNumber($request->attributes->get('email'), $request->attributes->get('number'), withVariants: true), Response::HTTP_OK)
            );
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
