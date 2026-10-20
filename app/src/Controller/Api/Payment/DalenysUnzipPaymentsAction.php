<?php

namespace App\Controller\Api\Payment;

use App\Legacy\Dalenys\DalenysFileHandler;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class DalenysUnzipPaymentsAction
{
    public function __construct(
        private readonly ViewHandlerInterface $viewHandler,
        private readonly DalenysFileHandler $dalenysFileHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('FILE');
        Assert::notNull($file);

        $this->dalenysFileHandler->handlePaymentsFile($file);

        return $this->viewHandler->handle(View::create([]));
    }
}
