<?php

namespace App\Controller\Admin\Action;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Factory\OrderFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Controller\EventDispatcherInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class BisCreationWithExchangeAction
{
    public const BIS_TYPE_EXCHANGE = 'bis-exchange';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $orderManager,
        private readonly OrderFactoryInterface $orderFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly OrderProcessorInterface $orderProcessor,
    ) {
    }

    public function __invoke(RequestConfiguration $configuration, Order $order): Response
    {
        $request = $configuration->getRequest();

        $firstShipment = $order->getShipments()->first();
        Assert::notFalse($firstShipment, 'Exchange must have a shipment');
        Assert::notNull($firstShipment->getMethod('Shipment\'s method cannot be null'));

        if (ShippingMethodInterface::METHOD_POSTE === $firstShipment->getMethod()->getCode()) {
            return new Response($this->translator->trans('sylius_admin_order_creation.ui.bis_exchange_create_error'), 403);
        }

        $this->orderManager->flush();

        /** @var Order $bisOrder */
        $bisOrder = $this->orderFactory->createReorder($order, 'bis', 'bis-exchange');

        $this->orderManager->persist($bisOrder);

        // Temporary fix because itemUnits and shipping adjustments have a different shipments than the order itself
        foreach ($bisOrder->getShipments() as $shipment) {
            foreach ($bisOrder->getItemUnits() as $itemUnit) {
                $shipment->addUnit($itemUnit);
            }

            /** @var Adjustment $adjustment */
            foreach ($bisOrder->getAdjustments() as $adjustment) {
                if ($adjustment->getShipment() !== $shipment) {
                    $adjustment->setShipment($shipment);
                }
            }
        }

        $request->request->add(['bisType' => self::BIS_TYPE_EXCHANGE]);

        $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::CREATE, $configuration, $bisOrder);

        if ($event->isStopped() && !$configuration->isHtmlRequest()) {
            return new Response($this->translator->trans('sylius_admin_order_creation.ui.bis_exchange_create_error'), 403);
        }

        $this->orderManager->flush();

        $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::CREATE, $configuration, $bisOrder);

        $postEventResponse = $postEvent->getResponse();
        if (null !== $postEventResponse) {
            return new Response($this->translator->trans('sylius_admin_order_creation.ui.bis_exchange_create_error'), 403);
        }

        $this->orderProcessor->process($bisOrder);

        return new Response('', 201);
    }
}
