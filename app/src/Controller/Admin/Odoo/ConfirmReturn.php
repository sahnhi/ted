<?php

declare(strict_types=1);

namespace App\Controller\Admin\Odoo;

use App\ERP\Application\Command\CommandBusInterface;
use App\ERP\Application\Command\Return\ConfirmReturnCommand;
use App\Services\Sentry\ReportingInterface;
use Sentry\Severity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

final class ConfirmReturn
{
    public const ROUTE = 'app_admin_odoo_confirm_return'; // Use it in attributes when available

    /**
     * @param Session $session
     */
    public function __construct(
        private readonly ReportingInterface $reporting,
        private readonly SessionInterface $session,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    /**
     * @Route("/admin/odoo/orders/{id}/confirm_return", name="app_admin_odoo_confirm_return", methods={"POST", "GET"})
     */
    public function __invoke(Request $request, string $id): Response
    {
        try {
            $this->commandBus->handle(new ConfirmReturnCommand((int) $id));
        } catch (\Throwable $e) {
            $this->reporting->reportIssue(
                issue: $e,
                severity: Severity::error(),
                tags: ['odoo' => self::class],
                extras: ['orderId' => $id],
            );

            $this->session->getFlashBag()->add(
                type: 'error',
                message: $e->getMessage()
            );
        } finally {
            return new RedirectResponse(
                url: $request->headers->get('referer'),
                status: Response::HTTP_FOUND,
            );
        }
    }
}
