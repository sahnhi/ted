<?php

namespace App\Controller\Admin\Action;

use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Services\OrderItem\OrderItemService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class GenerateOrderItemLogisticAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderItemService $orderItemService,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function __invoke(string $id): Response
    {
        $order = $this->orderRepository->findOneById($id);
        Assert::notNull($order, 'Order null');

        $this->orderItemService->generateItemLogistic($order);

        return new RedirectResponse(
            $this->urlGenerator->generate('sylius_admin_order_show', ['id' => $id])
        );
    }
}
