<?php

namespace App\Controller\Api\Cart;

use App\Factory\View\ValidationErrorViewFactoryInterface;
use App\Request\RemoveCouponRequest;
use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RemoveCouponAction
{
    public function __construct(
        private readonly CommandBus $bus,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorViewFactoryInterface $validationErrorViewFactory,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $removeCouponRequest = new RemoveCouponRequest($request);
        $validationResults = $this->validator->validate($removeCouponRequest);

        if (0 === count($validationResults)) {
            $removeCouponCommand = $removeCouponRequest->getCommand();

            try {
                $this->bus->handle($removeCouponCommand);

                return $this->viewHandler->handle(
                    View::create($this->cartQuery->getOneByToken($removeCouponCommand->orderToken()), Response::HTTP_OK)
                );
            } catch (\InvalidArgumentException $exception) {
                throw new BadRequestHttpException($exception->getMessage());
            }
        }

        return $this->viewHandler->handle(View::create($this->validationErrorViewFactory->create($validationResults), Response::HTTP_BAD_REQUEST));
    }
}
