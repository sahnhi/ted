<?php

namespace App\Controller\Api\Cart;

use App\Exception\Api\Cart\EmptyBodyException;
use App\Exception\Api\Cart\InvalidBodyException;
use App\Request\PutVariantBasedConfigurableItemToCartRequest;
use App\View\ValidationErrorView;
use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PutItemsAction
{
    public function __construct(
        private readonly CommandBus $bus,
        private readonly ValidatorInterface $validator,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $token = $request->attributes->get('token');
        $validationResults = [];
        $commandRequests = [];
        if ([] === $request->request->all()) {
            throw new EmptyBodyException('Request body is not supposed to be empty');
        }

        foreach ($request->request->all() as $item) {
            if (!is_array($item)) {
                throw new InvalidBodyException('Request body should be an array');
            }
            $item['token'] = $token;
            $item['channelCode'] = $request->attributes->get('channelCode');
            $commandRequest = $this->provideCommandRequest($item);
            $validationResult = $this->validator->validate($commandRequest);

            if (0 !== count($validationResult)) {
                $validationResults[] = $validationResult;

                continue;
            }

            $commandRequests[] = $commandRequest;
        }

        if (!$this->isValid($validationResults)) {
            $errorMessage = new ValidationErrorView();
            $errorMessage->code = Response::HTTP_BAD_REQUEST;
            $errorMessage->message = 'Validation failed';

            foreach ($validationResults as $validationResult) {
                $errors = [];

                /** @var ConstraintViolationInterface $result */
                foreach ($validationResult as $result) {
                    $errors[$result->getPropertyPath()][] = $result->getMessage();
                }

                $errorMessage->errors[] = $errors;
            }

            return $this->viewHandler->handle(View::create($errorMessage, Response::HTTP_BAD_REQUEST));
        }

        foreach ($commandRequests as $commandRequest) {
            $this->bus->handle($commandRequest->getCommand());
        }

        try {
            return $this->viewHandler->handle(View::create($this->cartQuery->getOneByToken($token), Response::HTTP_CREATED));
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    private function provideCommandRequest(array $item): PutVariantBasedConfigurableItemToCartRequest
    {
        if (isset($item['variantCode']) && !isset($item['options'])) {
            return PutVariantBasedConfigurableItemToCartRequest::fromArray($item);
        }

        throw new NotFoundHttpException('Variant not found for given configuration');
    }

    private function isValid(array $validationResults): bool
    {
        foreach ($validationResults as $validationResult) {
            if (0 !== count($validationResult)) {
                return false;
            }
        }

        return true;
    }
}
