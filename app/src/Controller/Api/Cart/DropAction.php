<?php

namespace App\Controller\Api\Cart;

use App\Factory\View\ValidationErrorViewFactoryInterface;
use App\Request\DropCartRequest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DropAction
{
    public function __construct(
        private readonly CommandBus $bus,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorViewFactoryInterface $validationErrorViewFactory,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $pickupRequest = new DropCartRequest($request);

        $validationResults = $this->validator->validate($pickupRequest);

        if (0 === count($validationResults)) {
            $this->bus->handle($pickupRequest->getCommand());

            return $this->viewHandler->handle(View::create(null, Response::HTTP_NO_CONTENT));
        }

        return $this->viewHandler->handle(View::create($this->validationErrorViewFactory->create($validationResults), Response::HTTP_BAD_REQUEST));
    }
}
