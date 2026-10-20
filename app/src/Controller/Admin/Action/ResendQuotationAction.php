<?php

namespace App\Controller\Admin\Action;

use App\Doctrine\Entity\Sylius\Address;
use App\Message\DTO\OrderDTO;
use App\Message\Mail;
use App\Services\Mail\MailerEmailInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class ResendQuotationAction
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly MessageBusInterface $bus,
        private readonly FlashBagInterface $flashBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(string $id): Response
    {
        $cart = $this->orderRepository->find($id);
        Assert::notNull($cart);

        if (null === $cart->getShippingAddress()) {
            $cart->setShippingAddress(new Address());
        }

        $cart->setBillingAddress($cart->getShippingAddress());

        $mailer = new Mail(MailerEmailInterface::TYPE_QUOTATION_EMAIL, new OrderDTO($cart), [
            'recipients' => [
                [
                    'email' => $cart->getCustomer()->getEmail(),
                    'firstname' => $cart->getCustomer()->getFirstName(),
                    'lastname' => $cart->getCustomer()->getLastName(),
                ],
            ],
        ]);

        try {
            $this->bus->dispatch($mailer);

            $this->flashBag->add(
                'success',
                $this->translator->trans('sylius.ui.quotation_resend.confirmation')
            );
        } catch (\Exception) {
            $this->flashBag->add(
                'failed',
                $this->translator->trans('sylius.ui.quotation_resend.error')
            );
        }

        return new RedirectResponse($this->urlGenerator->generate('sylius_admin_order_store_index', []));
    }
}
