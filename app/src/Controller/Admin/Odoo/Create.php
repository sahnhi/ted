<?php

declare(strict_types=1);

namespace App\Controller\Admin\Odoo;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\ERP\Application\Command\CommandBusInterface;
use App\ERP\Application\Command\Order\CreateOrderCommand;
use App\ERP\Application\Command\Return\CreateReturnCommand;
use App\Services\Channel\ChannelCode;
use App\Services\Sentry\ReportingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sentry\Severity;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

final class Create
{
    public const ROUTE = 'app_admin_odoo_create'; // Use it in attributes when available

    /**
     * @param OrderRepository $orderRepository
     * @param Session         $session
     */
    public function __construct(
        private readonly ReportingInterface $reporting,
        private readonly RepositoryInterface $orderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SessionInterface $session,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    /**
     * @Route("/admin/odoo/orders/{id}/create", name="app_admin_odoo_create", methods={"POST", "GET"})
     */
    public function __invoke(Request $request, string $id): Response
    {
        try {
            $order = $this->orderRepository->find($id);
            if (ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode()) {
                $this->commandBus->handle(new CreateReturnCommand($order->getId()));
            } else {
                $this->commandBus->handle(new CreateOrderCommand($order->getId()));
            }
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->reporting->reportIssue(
                issue: $e,
                severity: Severity::error(),
                tags: ['odoo' => self::class],
                extras: ['orderId' => $id],
            );

            $this->session->getFlashBag()->add(
                type: 'error',
                message: $e->getMessage(),
            );
        } finally {
            return new RedirectResponse(
                url: $request->headers->get('referer'),
                status: Response::HTTP_FOUND,
            );
        }
    }
}
