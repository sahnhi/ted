<?php

namespace App\Controller\Api\Order;

use App\Services\Transmission\Tracking\AssignTrackingUrl;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class TrackingAction
{
    public function __construct(
        private readonly ViewHandlerInterface $viewHandler,
        private readonly AssignTrackingUrl $assignTrackingUrl,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): Response
    {
        Assert::notNull($request->attributes->get('token'), 'token is required');
        Assert::notNull($request->request->get('trackingUrl'), 'trackingUrl is required');

        $this->assignTrackingUrl->process($request->attributes->get('token'), $request->request->get('trackingUrl'));
        $this->entityManager->flush();

        return $this->viewHandler->handle(View::create([]));
    }
}
