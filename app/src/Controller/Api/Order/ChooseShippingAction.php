<?php

namespace App\Controller\Api\Order;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use App\Business\Command\ChooseShipping;
use App\Doctrine\Entity\Sylius\Order;
use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ChooseShippingAction
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler,
        private readonly NormalizerInterface $normalizer,
        private readonly SerializerContextBuilderInterface $contextBuilder,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request): Response
    {
        $chooseShipping = new ChooseShipping();
        $chooseShipping->createFromShopRequest($request, $request->attributes->get('token'));

        /** @var mixed|Order $order */
        $order = $this->commandBus->handle($chooseShipping);

        if ($order instanceof Order) {
            $context = $this->contextBuilder->createFromRequest($request, true, [
                'resource_class' => $order::class,
                'item_operation_name' => 'shop_get',
            ]);

            $context['groups'] = ['shop:cart:read'];

            return new JsonResponse($this->normalizer->normalize($order, 'jsonld', $context));
        }

        try {
            return $this->viewHandler->handle(
                View::create($this->cartQuery->getOneByToken($request->attributes->get('token')), Response::HTTP_OK)
            );
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
