<?php

declare(strict_types=1);

namespace App\Services\Olvo;

use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\Component\Core\Model\OrderItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address as MailerAddress;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OlvoApi implements OlvoApiInterface
{
    public function __construct(
        private readonly HttpClientInterface $olvoClient,
        private readonly MailerInterface $mailer,
        private readonly ReportingInterface $reporting,
        private readonly PackageTypeResolverInterface $packageTypeResolver,
    ) {
    }

    public function sendOrder(Order $order): void
    {
        $payload = $this->setArrayDatas($order);

        try {
            $this->olvoClient->request(Request::METHOD_POST, '/api/v2/deliveries/', [
                'json' => $payload,
            ]);
        } catch (TransportExceptionInterface|\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['service' => 'olvo'],
                [
                    'order_number' => $order->getNumber(),
                    'payload_send' => $payload,
                ]
            );

            $reference = ltrim((string) $order->getNumber(), '0');
            $message = (new Email())
                ->subject(sprintf('Commande Olvo %s non transmise', $reference))
                ->from(new MailerAddress('support@tediber.com', 'TEDIBER Sylius'))
                ->to('sav@tediber.com', 'dev@tediber.com')
                ->html(
                    'La commande n° ' . $reference . " n'a pas été transmise à Pedalo.<br /><br />
                Erreur: " . $e->getMessage() . "<br /><br />
                L'équipe IT",
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }

    /**
     * @throws \InvalidArgumentException
     * @throws PackageTypeNotFoundException
     */
    public function setArrayDatas(Order $order): array
    {
        /** @var Address|null $shippingAddress */
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            throw new \InvalidArgumentException('Order shipping address cannot be null.');
        }

        $packages = [];
        $comments = '';

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            $packages[] = [
                'package_type_id' => $this->packageTypeResolver->resolvePackageType($item),
                'amount' => $item->getQuantity(),
            ];
            $comments .= sprintf('%s x %s Ref %s, ', $item->getQuantity(), $item->getVariantName(), $item->getVariant()->getCode());
        }
        $comments = substr($comments, 0, -2);
        $street = explode("\n", (string) $shippingAddress->getStreet(), 2);
        $instructions = (!empty($street[1]) ? str_replace("\n", '', $street[1]) . ' ' : '') . (!empty($shippingAddress->getDetails()) ? $shippingAddress->getDetails() : '');

        /** @var iterable<Shipment> $shipments */
        $shipments = $order->getShipments();

        $startDeliverySlot = $shipments[0]->getStartDeliverySlot();
        $endDeliverySlot = $shipments[0]->getEndDeliverySlot();

        return [
            'comments' => $comments,
            'dropoff' => [
                'slot_starting_at' => $startDeliverySlot?->format('Y-m-d H:i:s') ?? '',
                'slot_ending_at' => $endDeliverySlot?->format('Y-m-d H:i:s') ?? '',
                'place' => [
                    'recipient_name' => sprintf('%s %s', $shippingAddress->getFirstName(), $shippingAddress->getLastName()),
                    'recipient_phone' => $shippingAddress->getPhoneNumber(),
                    'company_name' => !empty($shippingAddress->getCompany()) ? $shippingAddress->getCompany() : '',
                    'address' => !empty($street[0]) ? $street[0] : '',
                    'postal_code' => $shippingAddress->getPostCode(),
                    'city' => $shippingAddress->getCity(),
                    'address_instructions' => $instructions,
                ],
            ],
            'packages' => $packages,
        ];
    }
}
