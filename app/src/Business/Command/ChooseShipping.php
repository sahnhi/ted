<?php

namespace App\Business\Command;

use App\Calendar\Command\SelectCalendarSlotForAutonomousReturn;
use App\Doctrine\Entity\Sylius\Order;
use Symfony\Component\HttpFoundation\Request;

final class ChooseShipping
{
    private string $orderToken;
    private string $method;
    private ?\DateTime $startDeliverySlot = null;
    private ?\DateTime $endDeliverySlot = null;
    private ?int $idShippingMethodBalou = null;
    private ?string $relayId = null;

    private $relayAddress;

    public function getOrderToken(): string
    {
        return $this->orderToken;
    }

    public function setOrderToken(string $orderToken): ChooseShipping
    {
        $this->orderToken = $orderToken;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): ChooseShipping
    {
        $this->method = $method;

        return $this;
    }

    public function getStartDeliverySlot(): ?\DateTime
    {
        return $this->startDeliverySlot;
    }

    public function setStartDeliverySlot(?\DateTime $startDeliverySlot): ChooseShipping
    {
        $this->startDeliverySlot = $startDeliverySlot;

        return $this;
    }

    public function getEndDeliverySlot(): ?\DateTime
    {
        return $this->endDeliverySlot;
    }

    public function setEndDeliverySlot(?\DateTime $endDeliverySlot): ChooseShipping
    {
        $this->endDeliverySlot = $endDeliverySlot;

        return $this;
    }

    public function getIdShippingMethodBalou(): ?int
    {
        return $this->idShippingMethodBalou;
    }

    public function setIdShippingMethodBalou(?int $idShippingMethodBalou): ChooseShipping
    {
        $this->idShippingMethodBalou = $idShippingMethodBalou;

        return $this;
    }

    public function getRelayId(): ?string
    {
        return $this->relayId;
    }

    public function setRelayId(?string $relayId): ChooseShipping
    {
        $this->relayId = $relayId;

        return $this;
    }

    public function getRelayAddress(): ?array
    {
        return $this->relayAddress;
    }

    public function setRelayAddress(?array $relayAddress): ChooseShipping
    {
        $this->relayAddress = $relayAddress;

        return $this;
    }

    public function createFromShopRequest(Request $request, $orderToken)
    {
        $this->orderToken = $orderToken;
        $this->method = $request->request->get('method');
        $this->startDeliverySlot = (null !== $request->request->get('start_slot')) ? \DateTime::createFromFormat('Y-m-d H:i:s', $request->request->get('start_slot')) : null;
        $this->endDeliverySlot = (null !== $request->request->get('end_slot')) ? \DateTime::createFromFormat('Y-m-d H:i:s', $request->request->get('end_slot')) : null;
        $this->idShippingMethodBalou = $request->request->get('erp_id');
        $this->relayId = $request->request->get('relay_id', null);
        $this->relayAddress = !empty($request->request->get('relay_address')) ?
            $request->request->get('relay_address') :
            null;
    }

    /**
     * @throws \Exception
     */
    public function createFromAdminRequest(Request $request, Order $order): void
    {
        $data = $request->get('sylius_admin_order_creation_new_order');
        if (null === $data
            || empty($data['shipments'])
            || empty($data['shipments'][0])) {
            throw new \Exception('Cannot create order without shipments');
        }
        $shipmentData = $data['shipments'][0];

        $this->orderToken = $order->getTokenValue();
        $this->method = $shipmentData['method'];
        $this->startDeliverySlot = $this->createDateTimeFromAdminRequest($shipmentData['startDeliverySlot']);
        $this->endDeliverySlot = $this->createDateTimeFromAdminRequest($shipmentData['endDeliverySlot']);
        $this->idShippingMethodBalou = !empty($shipmentData['idShippingMethodBalou']) ? $shipmentData['idShippingMethodBalou'] : null;
        $this->relayId = $shipmentData['relayId'] ?? null;

        if (!empty($shipmentData['relay'])) {
            $this->relayAddress = [
                'firstName' => $order->getCustomer()->getFirstName(),
                'lastName' => $order->getCustomer()->getLastName(),
                'company' => $shipmentData['relay']['company'],
                'street' => $shipmentData['relay']['street'],
                'postcode' => $shipmentData['relay']['postcode'],
                'city' => $shipmentData['relay']['city'],
                'countryCode' => $shipmentData['relay']['countryCode'],
                'details' => $shipmentData['relay']['details'],
            ];
        }
    }

    public static function createFromAutonomousReturn(SelectCalendarSlotForAutonomousReturn $command, $orderToken): self
    {
        $chooseShipping = new self();
        $chooseShipping->orderToken = $orderToken;
        $chooseShipping->method = $command->getMethod();
        $chooseShipping->startDeliverySlot = $command->getStartDeliverySlot();
        $chooseShipping->endDeliverySlot = $command->getEndDeliverySlot();
        $chooseShipping->idShippingMethodBalou = $command->getErpId();
        $chooseShipping->relayId = $command->getRelayId();
        $chooseShipping->relayAddress = $command->getRelayAddress();

        return $chooseShipping;
    }

    private function createDateTimeFromAdminRequest(array $data)
    {
        if (empty($data['date']) || empty($data['time']) || empty($data['time']['hour']) || empty($data['time']['minute'])) {
            return null;
        }
        $dateString = $data['date']
            . ' '
            . str_pad((string) $data['time']['hour'], 2, 0, STR_PAD_LEFT)
            . ':'
            . str_pad((string) $data['time']['minute'], 2, 0, STR_PAD_LEFT);

        return \DateTime::createFromFormat('Y-m-d H:i', $dateString);
    }
}
