<?php

namespace App\Controller\Api\Cart;

use App\Factory\View\ValidationErrorViewFactoryInterface;
use App\Request\PickupCartRequest;
use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PickupAction
{
    public function __construct(
        private readonly CommandBus $bus,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorViewFactoryInterface $validationErrorViewFactory,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): Response
    {
        $pickupRequest = new PickupCartRequest($request);
        $validationResults = $this->validator->validate($pickupRequest);

        if (0 === count($validationResults)) {
            $pickupCartCommand = $pickupRequest->getCommand();
            $this->bus->handle($pickupCartCommand);

            try {
                return $this->viewHandler->handle(
                    View::create($this->cartQuery->getOneByToken($pickupCartCommand->orderToken()), Response::HTTP_CREATED)
                );
            } catch (\InvalidArgumentException $exception) {
                throw new BadRequestHttpException($exception->getMessage());
            }
        }

        return $this->viewHandler->handle(View::create($this->validationErrorViewFactory->create($validationResults), Response::HTTP_BAD_REQUEST));
    }
}
