<?php

namespace App\Controller\Admin\Action;

use App\Services\Mail\GiftCard\GiftCardEmail;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class ResendGiftCardConfirmationEmailAction
{
    public function __construct(
        private readonly FlashBagInterface $flashBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly GiftCardEmail $giftCardEmail,
        private readonly GiftCardRepositoryInterface $giftCardRepository
    ) {
    }

    public function __invoke(string $id, $recipient = 0): Response
    {
        $giftCard = $this->giftCardRepository->findOneBy(['id' => $id]);
        Assert::true($giftCard->isEnabled());
        $orderItem = $giftCard->getOrderItemUnit()->getOrderItem();
        $order = $orderItem->getOrder();
        Assert::notNull($order->getNumber(), 'Cannot find the order number');

        try {
            $this->giftCardEmail->generateEmail($order, $orderItem, $recipient);

            $this->flashBag->add(
                'success',
                $this->translator->trans('sylius.ui.confirmation_resend.confirmation')
            );
        } catch (\Exception) {
            $this->flashBag->add(
                'failed',
                $this->translator->trans('sylius.ui.confirmation_resend.error')
            );
        }

        return new RedirectResponse($this->urlGenerator->generate('setono_sylius_gift_card_admin_gift_card_index'));
    }
}
