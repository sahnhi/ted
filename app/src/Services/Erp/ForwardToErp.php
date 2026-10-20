<?php

namespace App\Services\Erp;

use App\Doctrine\Entity\OrderOutOfStockInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Entity\Warehouse;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Message\DTO\OrderDTO;
use App\Message\Mail;
use App\Model\ProductReferenceInterface;
use App\Registry\EmailConfigurationRegistryInterface;
use App\Services\Mail\GiftCard\GiftCardEmail;
use App\Services\Mail\MailerEmailInterface;
use App\Services\Sentry\Reporting;
use App\Services\Transmission\OrderItemComboProductService;
use App\Services\Transmission\PrepareTransmission;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Exception\GuzzleException;
use Sentry\Severity;
use Sylius\Component\Core\OrderPaymentStates;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ForwardToErp
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em,
        private readonly PrepareTransmission $prepareTransmission,
        private readonly OrderItemComboProductService $orderItemComboProductService,
        private readonly MessageBusInterface $bus,
        private readonly GiftCardEmail $giftCardEmail,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry,
        private readonly Reporting $reporting,
    ) {
    }

    /**
     * @throws EmailConfigurationNotFoundException
     * @throws TransportExceptionInterface
     * @throws NonUniqueResultException
     * @throws GuzzleException
     */
    public function postComplete(Order $order): void
    {
        if (OrderPaymentStates::STATE_PAID !== $order->getPaymentState()
            || $order->getErpForwarded()
            || $this->giftCardOrder($order)
        ) {
            return;
        }

        $backProductArray = $variantNoShipping = [];
        /** @var iterable<ShipmentInterface> $shipments */
        $shipments = $order->getShipments();

        /** @var ShippingMethodInterface $shipment */
        $shipment = $shipments[0]?->getMethod();

        $shipmentTmp = false;
        foreach ($order->getItems() as $item) {
            /** @var OrderItem $item */
            if (!$item->getVariant()->isShippingRequired()) {
                $variantNoShipping[] = $item;
                continue;
            }

            $productCodes = explode('_', (string) $item->getProduct()->getCode());
            foreach ($productCodes as $productCode) {
                $this->orderItemComboProductService->addItemProductCombo($item, $productCode);

                if (in_array($productCode, ProductReferenceInterface::REPOSSESS_REFS)) {
                    $backProductArray[] = $productCode;
                }
            }
        }

        $reference = ltrim((string) $order->getNumber(), '0');
        /* Alert for store's order with variant without shippingRequired */
        if (null !== $shipment && !empty($variantNoShipping) && (!empty($order->getOrderStore()) && !empty($order->getOrderStore()->getStore()))) {
            /** @var Warehouse|null $warehouse */
            $warehouse = $shipment->getWarehouse();

            if ((null !== $warehouse && null !== $warehouse->getFtpConfigurationName()) || ShippingMethodInterface::METHOD_OLVO === $shipment->getCode()) {
                $this->alertHasVariantStore($order, $variantNoShipping, $reference);
            }
        }

        if (1 < count($shipments) && 'POSTE' !== $shipment->getCode()) {
            foreach ($shipments as &$ship) {
                if (null !== $ship->getStartDeliverySlot() && null !== $ship->getEndDeliverySlot()) {
                    $shipmentTmp = $ship;
                } else {
                    $order->removeShipment($ship);
                }
            }
            $shipment = $shipmentTmp->getMethod() ?? $shipment;

            $message = (new Email())
                ->subject(sprintf('Alerte commande %s', $reference))
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::TRANSMISSION_PROBLEM)->getPrincipals())
                ->text(
                    'Problème plusieurs transporteur pour la commande n°' . $reference . ", veuillez vérifier la transmission s'il vous plait."
                );

            $this->mailer->send($message);
        }

        try {
            $order->setErpForwarded(true);
            $this->em->persist($order);
            $this->em->flush();

            if (!in_array($order->getChannel()->getCode(), ['bis', 'return'])
                && null !== $shipment
                && !in_array($shipment->getCode(), ['COLISWEB', 'MAGASIN'])) {
                $orderTransformer = new OrderDTO($order);

                // send order confirmation email except for bis and return orders
                $mailer = new Mail(MailerEmailInterface::TYPE_ORDER_CONFIRMATION_EMAIL, $orderTransformer, [
                    'recipients' => [
                        [
                            'email' => $order->getCustomer()->getEmail(),
                            'firstname' => $order->getCustomer()->getFirstName(),
                            'lastname' => $order->getCustomer()->getLastName(),
                        ],
                    ],
                ]);
                $this->bus->dispatch($mailer);
            }

            if (!(empty($backProductArray))) {
                // send returning product email
                $mailer = new Mail(MailerEmailInterface::TYPE_RETURNING_PRODUCTS_EMAIL, $order, [
                    'recipients' => [
                        [
                            'email' => $order->getCustomer()->getEmail(),
                            'firstname' => $order->getCustomer()->getFirstName(),
                            'lastname' => $order->getCustomer()->getLastName(),
                        ],
                    ],
                ]);
                $mailer->setData($backProductArray);
                $this->bus->dispatch($mailer);
            }
            if (null !== $shipment) {
                $this->prepareTransmission->generateTransmission($order);
            }
        } catch (\Exception $exception) {
            $payload = [
                'cartToken' => $order->getTokenValue(),
                'reference' => $reference,
                'email' => $order->getCustomer()->getEmail(),
                'commentaire' => $order->getNotes(),
            ];
            $message = (new Email())
                ->subject('Erreur de transmission commande ' . $exception->getMessage())
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to('dev@tediber.com')
                ->text(
                    sprintf('Problème de transmission de la commande n°%s %s', $reference, json_encode($payload)),
                );

            $this->reporting->reportIssue($exception, Severity::warning(),
                [
                    'cartToken' => $order->getTokenValue(),
                    'reference' => $reference,
                    'email' => $order->getCustomer()->getEmail(),
                ]);

            $this->mailer->send($message);
        }
    }

    public function orderPlanned(Order $order)
    {
        if (OrderPaymentStates::STATE_PAID !== $order->getPaymentState()) {
            return;
        }

        if (!empty($order->getOrderOutOfStock())
            && OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED === $order->getOrderOutOfStock()->getStatus()) {
            $orderOutOfStock = $order->getOrderOutOfStock();
            $orderOutOfStock->setStatus(OrderOutOfStockInterface::STATE_PLANNED);

            $this->em->persist($orderOutOfStock);
            $this->em->flush();
        }
    }

    private function giftCardOrder(Order $order): bool
    {
        foreach ($order->getItems() as $item) {
            /** @var OrderItem $item */
            if (true === $item->getVariant()->getProduct()->isGiftCard()) {
                // Send to buyer
                $this->giftCardEmail->generateEmail($order, $item);

                // Send to receiver
                $this->giftCardEmail->generateEmail($order, $item, true);

                $order->setErpForwarded(true);
                $this->em->persist($order);
                $this->em->flush();

                return true;
            }
        }

        return false;
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function alertHasVariantStore(Order $order, array $variantNoShipping, string $reference): void
    {
        $storeName = $order->getOrderStore()->getStore()->getName();
        if ('STE_CROIX' === $storeName) {
            $storeName = 'sainte-croix';
        }
        $setTo = [
            'sav@tediber.com',
            sprintf('%s.store@tediber.com', $storeName),
        ];
        $listVariant = '';
        $refText = (count($variantNoShipping) > 1) ? 'des références ' : 'de la référence ';
        foreach ($variantNoShipping as $item) {
            $listVariant .= $item->getVariant()->getCode() . ', ';
        }
        $body = 'Bonjour<br /><br />';
        $body .= 'La livraison ' . $refText . $listVariant . 'effectuée dans la boutique de ' . $storeName . ' ne pourra pas avoir lieu.<br />';
        $body .= "@équipe SAV : merci d'effectuer la bis correspondante.<br /><br />";
        $body .= 'Cordialement,<br />Tediber';
        $message = (new Email())
            ->subject(sprintf('Livraison impossible n°%s', $reference))
            ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
            ->to(...$setTo)
            ->html($body);

        $this->mailer->send($message);
    }
}
