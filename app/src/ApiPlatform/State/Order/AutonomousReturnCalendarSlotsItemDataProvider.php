<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Order;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Calendar\ViewModels\CalendarView;
use App\Calendar\ViewModels\Factory\CalendarSlotsFactory;
use App\Services\OrderReturn\ReturnType\AutonomousService;
use App\Services\Token\Checker\Autonomous;
use App\Services\Token\TokenException;
use App\Services\Token\TokenService;
use App\ViewRepository\CartViewRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class AutonomousReturnCalendarSlotsItemDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    public function __construct(
        private readonly TokenService $tokenService,
        private readonly AutonomousService $autonomousService,
        private readonly CartViewRepository $cartViewRepository,
        private readonly CalendarSlotsFactory $calendarSlotsFactory
    ) {
    }

    public function getItem(string $resourceClass, $id, ?string $operationName = null, array $context = []): CalendarView
    {
        try {
            $this->tokenService->checkToken(Autonomous::class, $id, $context['app_query_params']['email']);
        } catch (TokenException $e) {
            throw new UnauthorizedHttpException('Basic', $e->getMessage());
        }

        $calendar = $this->autonomousService->getCalendar($id);

        return new CalendarView($this->calendarSlotsFactory->create($calendar), $this->cartViewRepository->getOneByToken($id));
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return 'autonomous_return_calendar_slots' === $operationName;
    }
}
