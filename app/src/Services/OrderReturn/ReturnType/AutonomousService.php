<?php

namespace App\Services\OrderReturn\ReturnType;

use App\Api\Odoo\Model\OrderInterface as OdooOrderInterface;
use App\Component\Core\OrderShippingStates;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\ShippingMethodCategoryRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Factory\Calendar\Request\CalendarAvailabilityRequestFactory;
use App\Message\Mail;
use App\Services\Calendar\CalendarApi;
use App\Services\Erp\ForwardToErp;
use App\Services\Mail\ReturnMail\AutonomousReturnEmail;
use App\Services\OrderReturn\ReturnMailService;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use App\Services\Sentry\ReportingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sentry;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Messenger\MessageBusInterface;

class AutonomousService
{
    public function __construct(
        private readonly CalendarApi $calendarService,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly OrderRepository $orderRepository,
        private readonly MessageBusInterface $bus,
        private readonly ReturnMailService $returnMailService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ForwardToErp $forwardToErp,
        private readonly ReportingInterface $reporting,
        private readonly CalendarAvailabilityRequestFactory $calendarAvailabilityRequestFactory,
        private readonly ShippingMethodCategoryRepository $shippingMethodCategoryRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function checkCarrier(array $orderData): void
    {
        $request = $this->calendarAvailabilityRequestFactory->createForAutonomousCreate($orderData);

        $lastDate = $request->getDate();

        foreach ($request->getProducts() as $product) {
            $lastDate = $request->isWithoutStock() ?
                $lastDate :
                $this->productVariantRepository->getFirstAvailableShippingDate($product['code'], $lastDate);
        }

        $request->setDate($lastDate);
        $this->calendarService->setRequest($request);
        $times = $this->calendarService->checkCalendar();

        if (!empty($times) && ($carrier = array_shift(current($times)['times'])['providerName']) === ShippingMethodInterface::METHOD_POSTE) {
            throw new BadRequestException(sprintf('Carrier not supported for autonomous return : %s', $carrier));
        }
    }

    /**
     * @throws \Exception
     */
    public function getCalendar(string $orderToken): array
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $orderToken]);

        $request = $this->calendarAvailabilityRequestFactory->createForAutonomousReturn($order);

        $lastDate = $request->getDate();

        foreach ($request->getProducts() as $product) {
            $lastDate = $request->isWithoutStock() ?
                $lastDate :
                $this->productVariantRepository->getFirstAvailableShippingDate($product['code'], $lastDate);
        }

        $request->setDate($lastDate);
        $this->calendarService->setRequest($request);

        return $this->calendarService->checkCalendar();
    }

    public function postCreate(Order $order): void
    {
        if (ReturnTypeServiceInterface::RETURN_TYPE_AUTONOMOUS === $order->getReturnType(true)) {
            /** @var ShippingMethodCategory $shippingMethodCategory */
            $shippingMethodCategory = $this->shippingMethodCategoryRepository->findOneBy([
                'code' => 'RETURN',
            ]);
            /** @var Shipment $shipment */
            foreach ($order->getShipments() as $shipment) {
                $shipment->setShippingMethodCategory($shippingMethodCategory);
            }

            $this->bus->dispatch(new Mail(AutonomousReturnEmail::class, $order));
        }
    }

    public function postShippingSelected(Order $order): void
    {
        if (ReturnTypeServiceInterface::RETURN_TYPE_AUTONOMOUS === $order->getReturnType(true)) {
            $order->setShippingState(OrderShippingStates::STATE_READY);
            foreach ($order->getShipments() as $shipment) {
                /* @var Shipment $shipment */
                $shipment->setState(OrderShippingStates::STATE_READY);
            }
            $order->setState(OrderInterface::STATE_CART);
            $order->setCheckoutState(OrderCheckoutStates::STATE_ADDRESSED);
            $order->setOdooState(OdooOrderInterface::ODOO_STATE_READY);
            $this->entityManager->persist($order);

            try {
                $this->forwardToErp->postComplete($order);
                $this->returnMailService->process($order);
            } catch (\Exception $exception) {
                $this->reporting->reportIssue(
                    $exception,
                    Sentry\Severity::error(),
                    ['order' => $order->getNumber()]
                );
            }
        }
    }
}
