<?php

declare(strict_types=1);

namespace App\Services\OrderReturn;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Message\DTO\OrderDTO;
use App\Message\Mail;
use App\Registry\EmailConfigurationRegistryInterface;
use App\Services\Channel\ChannelCode;
use App\Services\Colissimo\ColissimoApiInterface;
use App\Services\Mail\MailerEmailInterface;
use GuzzleHttp\ClientInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Webmozart\Assert\Assert;

class ReturnMailService
{
    private readonly ?Request $request;

    public function __construct(
        private readonly ColissimoApiInterface $colissimoApi,
        ClientInterface $erpClient,
        private readonly ReturnTypeServiceInterface $returnTypeService,
        private readonly MessageBusInterface $bus,
        RequestStack $requestStack,
        private readonly MailerInterface $mailer,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
        $this->colissimoApi->setErpClient($erpClient);
        $this->request = $requestStack->getCurrentRequest();
    }

    public function process(OrderInterface $order): void
    {
        if (null === $order->getChannel() || ChannelCode::CHANNEL_RETURN !== $order->getChannel()->getCode()) {
            return;
        }
        $methodCode = !empty($order->getShipments()->first()) ? $order->getShipments()->first()->getMethod()->getCode() : null;

        $noEmailSentTypes = [Reason::RETURN_TYPE_REFUND, Reason::RETURN_TYPE_SENDER, Reason::RETURN_TYPE_WAREHOUSE];

        if (!in_array($this->returnTypeService->getReturnType($order), $noEmailSentTypes)) {
            switch ($methodCode) {
                case ShippingMethodInterface::METHOD_POSTE:
                    $this->handleColissimo($order);
                    break;
                case str_contains((string) $methodCode, ShippingMethodInterface::METHOD_EDELIV):
                case str_contains((string) $methodCode, ShippingMethodInterface::METHOD_VIR):
                    $this->handleReturnProductMail($order);
                    break;
                case ShippingMethodInterface::METHOD_RETOURINTER:
                    $this->sendQ77ReturnInterMail($order);
                    break;
            }
        } else {
            switch ($this->returnTypeService->getReturnType($order)) {
                case Reason::RETURN_TYPE_SENDER:
                    $this->handleSenderShippingDate($order);
                    // Intentional no break
                    // no break
                case Reason::RETURN_TYPE_WAREHOUSE:
                    $form = $this->request->get('sylius_admin_order_creation_new_order');
                    Assert::notNull($form);

                    $notify = $form['q77Notify'] ?? false;

                    if (false !== $notify) {
                        $this->sendQ77ReturnSenderMail($order);
                        $order->setNotes($order->getNotes() . ' Un mail a été envoyé à quai77 pour demander le retour et la remise en stock des produits');
                    }
                    break;
            }
        }
    }

    private function handleColissimo(Order $order)
    {
        $files = $packageNumbers = [];

        for ($i = 0; $i < count($order->getItems()); ++$i) {
            [$file, $packageNumber] = $this->colissimoApi->getLabel($order, $order->getItems()[$i]);
            if (null !== $file) {
                $packageNumbers[] = $packageNumber;
                $files[] = [
                    'type' => 'application/pdf',
                    'name' => 'etiquette_retour-' . ($i + 1) . '.pdf',
                    'content' => base64_encode((string) $file->getContent()),
                ];
            }
        }

        $mailer = new Mail(MailerEmailInterface::TYPE_LABEL_EMAIL, $order, [
            'recipients' => [
                [
                    'email' => $order->getCustomer()->getEmail(),
                    'firstname' => $order->getCustomer()->getFirstName(),
                    'lastname' => $order->getCustomer()->getLastName(),
                ],
            ],
        ]);

        $mailer->setData([
            'returnOrder' => $this->returnTypeService->getReturnOrderPrefix($order),
            'templateOptions' => $this->returnTypeService->getTemplateOptions($order),
            'files' => $files,
        ]);

        $this->bus->dispatch($mailer);

        foreach ($packageNumbers as $index => $packageNumber) {
            if (0 !== $index && empty($order->getShipments()[$index])) {
                /** @var ShipmentInterface $shipment */
                $shipment = clone $order->getShipments()[0];
                $shipment->setTracking(ColissimoApiInterface::URL_TRACKING . $packageNumber);
                $shipment->setTrackingNumber($packageNumber);
                $order->addShipment($shipment);
            } else {
                /** @var ShipmentInterface $shipment */
                $shipment = $order->getShipments()[$index];
                $shipment->setTracking(ColissimoApiInterface::URL_TRACKING . $packageNumber);
                $shipment->setTrackingNumber($packageNumber);
            }
        }
    }

    private function handleReturnProductMail(Order $order)
    {
        $form = $this->request->get('sylius_admin_order_creation_new_order');
        $returnFormType = null !== $form ?
            ($form['returnType'] ?? null) :
            $order->getReturnType(true);

        $mailer = new Mail(MailerEmailInterface::TYPE_PRODUCT_RETURN_EMAIL, new OrderDTO($order), [
            'recipients' => [
                [
                    'email' => $order->getCustomer()->getEmail(),
                    'firstname' => $order->getCustomer()->getFirstName(),
                    'lastname' => $order->getCustomer()->getLastName(),
                ],
            ],
        ]);

        $mailer->setData([
            'returnOrder' => $this->returnTypeService->getReturnOrderPrefix($order),
            'templateOptions' => $this->returnTypeService->getTemplateOptions($order),
            'returnFormType' => $returnFormType,
        ]);

        $this->bus->dispatch($mailer);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    private function sendQ77ReturnInterMail(Order $order)
    {
        $shippingAddress = $order->getShippingAddress();

        $body = "Bonjour,<br/><br/>
                Merci de prendre en compte cette demande de retour INTER.<br/><br/>
                Adresse d'enlèvement : <br />
                " . $shippingAddress->getLastName() . ' ' . $shippingAddress->getFirstName() . '<br />
                ' . $order->getCustomer()->getEmail() . '<br />
                ' . $shippingAddress->getPhoneNumber() . '<br />
                ' . $shippingAddress->getStreet() . '<br />
                ' . $shippingAddress->getPostcode() . ' ' . $shippingAddress->getCity() . '<br />
                Pays : ' . $shippingAddress->getCountryCode() . '<br /><br />
                Numéro de commande : ' . ltrim((string) $order->getNumber(), '0') . '<br />
                Produits à retourner : <br />';

        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            $body .= $item->getQuantity() .
                ' - ' . $item->getVariantName() .
                ' - ' . $item->getVariant()->getHeight() . 'x' . $item->getVariant()->getWidth() . 'x' . $item->getVariant()->getDepth() .
                'cm - ' . $item->getVariant()->getWeight() . 'kg + ';

            $body .= str_starts_with((string) $item->getVariant()->getCode(), 'R') ? 'déballé' : 'emballé';
            $body .= '<br />';
        }

        $body .= "<br />La housse de protection a été envoyée au client de notre côté. <br />
            Merci de nous confirmer la prise en compte et nous indiquer le tarif, stp. <br /><br />
            Cordialement,
            L'équipe Tediber";

        $message = (new Email())
            ->subject('Nouveau retour international à traiter')
            ->from(new Address('sav@tediber.com', 'TEDIBER Sylius'))
            ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::INTERNATIONAL_RETURN)->getPrincipals())
            ->html($body);

        $this->mailer->send($message);
    }

    private function handleSenderShippingDate(Order $order)
    {
        if (!$order->getShipments()->isEmpty()) {
            /** @var Shipment $shipment */
            $shipment = $order->getShipments()->first();
            $start = new \DateTime();
            $interval = new \DateInterval('PT1H');

            $shipment->setStartDeliverySlot($start);
            $end = clone $start;
            $shipment->setEndDeliverySlot($end->add($interval));
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    private function sendQ77ReturnSenderMail(Order $order): void
    {
        $body = 'Hello,<br/><br/>
                Pouvez-vous demander le retour de la commande ' . ltrim((string) $order->getParentNumber(), '0') . " et la remettre en stock svp ?.<br/><br/>
                Merci de prendre en compte et de confirmer cette demande.<br /><br />
                Cordialement,<br /><br />
                L'équipe IT<br /><br />
                Tediber";

        $message = (new Email())
            ->subject(sprintf('Retour - Commande TED%s à remettre en stock', ltrim((string) $order->getParentNumber(), '0')))
            ->from(new Address('sav@tediber.com', 'TEDIBER Sylius'))
            ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::SENDER_RETURN)->getPrincipals())
            ->cc(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::SENDER_RETURN)->getCcs())
            ->html($body);

        $this->mailer->send($message);
    }
}
