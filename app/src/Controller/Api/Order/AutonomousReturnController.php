<?php

namespace App\Controller\Api\Order;

use App\Business\Command\ChooseShipping;
use App\Services\OrderReturn\ReturnType\AutonomousService;
use App\Services\Token\Checker\Autonomous;
use App\Services\Token\TokenException;
use App\Services\Token\TokenService;
use App\ViewRepository\CartViewRepository;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Webmozart\Assert\Assert;

class AutonomousReturnController
{
    public function __construct(
        private readonly TokenService $tokenService,
        private readonly AutonomousService $autonomousService,
        private readonly CartViewRepository $cartViewRepository,
        private readonly CommandBus $commandBus
    ) {
    }

    public function getCalendarSlots(Request $request): JsonResponse
    {
        $this->assertHasTokenParams($request);

        $token = $request->request->get('token');
        $email = $request->request->get('email');

        try {
            $this->tokenService->checkToken(Autonomous::class, $token, $email);
            $calendar = $this->autonomousService->getCalendar($token);

            $response = [
                'slots' => $calendar,
                'order' => $this->cartViewRepository->getOneByToken($token),
            ];

            return new JsonResponse($response);
        } catch (TokenException $e) {
            throw new UnauthorizedHttpException('Basic', $e->getMessage());
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    public function selectCalendarSlot(Request $request): JsonResponse
    {
        $this->assertHasTokenParams($request);

        $token = $request->request->get('token');
        $email = $request->request->get('email');

        try {
            $this->tokenService->checkToken(Autonomous::class, $token, $email);
            $chooseShipping = new ChooseShipping();
            $chooseShipping->createFromShopRequest($request, $token);
            $this->commandBus->handle($chooseShipping);

            return new JsonResponse();
        } catch (TokenException $e) {
            throw new UnauthorizedHttpException('Basic', $e->getMessage());
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    private function assertHasTokenParams(Request $request)
    {
        $token = $request->request->get('token');
        $email = $request->request->get('email');

        Assert::notEmpty($token, 'Token cannot be empty');
        Assert::notEmpty($email, 'Email cannot be empty');
    }
}
