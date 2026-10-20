<?php

namespace App\Controller\Api\Cart;

use App\Factory\View\ValidationErrorViewFactoryInterface;
use App\Request\ChangeItemQuantityRequest;
use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ChangeItemQuantityAction
{
    public function __construct(
        private readonly CommandBus $bus,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorViewFactoryInterface $validationErrorViewFactory,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler,
        private readonly LockFactory $lockFactory,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $changeItemQuantityRequest = new ChangeItemQuantityRequest($request);
        $validationResults = $this->validator->validate($changeItemQuantityRequest);

        if (0 !== count($validationResults)) {
            return $this->viewHandler->handle(View::create($this->validationErrorViewFactory->create($validationResults), Response::HTTP_BAD_REQUEST));
        }

        $lock = $this->lockFactory->createLock($changeItemQuantityRequest->getCommand()->orderToken(), 3.0);
        $lock->acquire(true);
        $changeItemQuantityCommand = $changeItemQuantityRequest->getCommand();

        try {
            $this->bus->handle($changeItemQuantityCommand);

            return $this->viewHandler->handle(
                View::create($this->cartQuery->getOneByToken($changeItemQuantityCommand->orderToken()), Response::HTTP_OK)
            );
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        } finally {
            $lock->release();
        }
    }
}
