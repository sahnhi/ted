<?php

namespace App\Controller\Api\Shipping;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Services\Transmission\Entrepot\Spidy\AbstractSpidy;
use App\Services\Transmission\Entrepot\Spidy\Deliver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use SM\SMException;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class UrbantzAction
{
    public const URBANTZ_DELIVERED = 'DELIVERED';

    public function __construct(
        private readonly EntityManagerInterface $orderManager,
        private readonly Deliver $deliver,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly string $urbantzTrackingUrl
    ) {
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws SMException
     */
    public function __invoke(Request $request): Response
    {
        $jsonArray = $request->request;

        /** Ensure Urbantz API compatibility */
        $jsonArray = !$jsonArray->has('TaskId') ? $jsonArray->all() : [$jsonArray->all()];

        foreach ($jsonArray as $task) {
            $number = $task['TaskId'] ?? null;
            Assert::notNull($number);

            /** @var Order $order */
            $order = $this->orderRepository->findOneLikeNumber($number);
            Assert::isInstanceOf($order, Order::class);

            $status = $task['Status'] ?? null;
            $tracking = $task['Tracking'] ?? null;
            $pod = $task['imagePath'] ?? null;
            $pod .= $task['execution']['successPicture'] ?? null;

            /** @var Shipment $shipment */
            $shipment = $order->getShipments()->first();
            $shipment->setProofOfDelivery($pod);

            if (null !== $tracking) {
                $shipment->setTracking($this->urbantzTrackingUrl . $tracking);
                $shipment->setTrackingNumber($tracking);

                $this->orderManager->persist($shipment);
            }

            if (null !== $status && self::URBANTZ_DELIVERED === $status) {
                $this->deliver->backupPayload(json_encode($jsonArray), AbstractSpidy::DELIVERED_FOLDER, $order->getNumber(), ShippingMethodInterface::METHOD_EDELIV);
                $this->deliver->setDelivered($order);
            }
        }

        $this->orderManager->flush();

        return new Response();
    }
}
