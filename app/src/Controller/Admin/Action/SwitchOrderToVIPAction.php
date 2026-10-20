<?php

namespace App\Controller\Admin\Action;

use App\Doctrine\Entity\OrderOutOfStockInterface;
use App\Doctrine\Entity\OrderVip;
use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class SwitchOrderToVIPAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $orderManager,
        private readonly FlashBagInterface $flashBag,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $orderVipManager
    ) {
    }

    public function __invoke(Request $request, string $id, bool $create): Response
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['id' => $id]);
        Assert::notNull($order);
        if (true === $create) {
            if (null !== $order->getOrderVip()) {
                return new RedirectResponse(
                    $this->urlGenerator->generate('sylius_admin_order_outofstock_index')
                );
            }
            $orderVip = new OrderVip();
            $orderVip->setOrder($order);

            $order->setOrderVip($orderVip);
            $order->setStockPriority($order->getStockPriority() + OrderOutOfStockInterface::VIP_WEIGHTING_VALUE);
        } elseif (null !== $order->getOrderVip()) {
            $this->orderVipManager->remove($order->getOrderVip());
            $order->setOrderVip(null);
            $order->setStockPriority($order->getStockPriority() - OrderOutOfStockInterface::VIP_WEIGHTING_VALUE);
        }

        $this->orderManager->persist($order);
        $this->orderManager->flush();

        $this->flashBag->add(
            'success',
            $this->translator->trans('sylius_admin_order_creation.ui.switch_to_vip')
        );

        return new RedirectResponse(
            $request->headers->get('referer')
        );
    }
}
