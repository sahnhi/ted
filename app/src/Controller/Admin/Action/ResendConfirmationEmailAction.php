<?php

namespace App\Controller\Admin\Action;

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

final class ResendConfirmationEmailAction
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly MessageBusInterface $bus,
        private readonly FlashBagInterface $flashBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(string $token): Response
    {
        $order = $this->orderRepository->findOneByTokenValue($token);
        Assert::notNull($order);

        $orderTransformer = new OrderDTO($order);

        $mailer = new Mail(MailerEmailInterface::TYPE_ORDER_CONFIRMATION_EMAIL, $orderTransformer, [
            'recipients' => [
                [
                    'email' => $order->getCustomer()->getEmail(),
                    'firstname' => $order->getCustomer()->getFirstName(),
                    'lastname' => $order->getCustomer()->getLastName(),
                ],
            ],
        ]);

        try {
            $this->bus->dispatch($mailer);

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

        return new RedirectResponse($this->urlGenerator->generate('sylius_admin_order_show', [
            'id' => $order->getId(),
        ]));
    }
}
