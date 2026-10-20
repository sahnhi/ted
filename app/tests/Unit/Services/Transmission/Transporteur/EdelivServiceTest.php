<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Transmission\Transporteur;

use App\Edeliv\Client\EdelivClientInterface;
use App\Services\Sentry\ReportingInterface;
use App\Services\Transmission\Transporteur\EDelivService;
use App\Tests\TestCase;

final class EdelivServiceTest extends TestCase
{
    public function testClientIsCallable(): void
    {
        $edelivClient = $this->createMock(EdelivClientInterface::class);
        $edelivService = new EDelivService(
            $edelivClient,
            $this->createMock(ReportingInterface::class)
        );
        $taskId = '123';

        $edelivClient->expects(self::once())
            ->method('cancelTask')
            ->with($taskId);

        $edelivService->callUrbantz($taskId);
    }
}
