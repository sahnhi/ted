<?php

namespace App\Controller\Api\Order\Review;

use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class NpsAction
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RepositoryInterface $orderRepository,
        private readonly RepositoryInterface $customerRepository,
        private readonly RepositoryInterface $productReviewRepository,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(Request $request): Response
    {
        if (!$order = $this->orderRepository->findOneBy([
            'tokenValue' => $request->request->get('cartToken'),
            'state' => Order::STATE_FULFILLED,
        ])) {
            $customer = $this->customerRepository->findOneByEmail($request->request->get('customer')['email']);

            Assert::notNull($customer, 'Customer with address email does not exists');

            if ($lastReview = $this->productReviewRepository->findOneBy(
                ['author' => $customer],
                ['id' => 'DESC']
            )) {
                $order = $lastReview->getOrder();
            }
        }

        Assert::notNull($order, 'Order with customer id does not exists');

        Assert::null($order->getNPS(), 'NPS already given for this order');

        $order->setNps($request->request->get('nps'));

        $this->orderRepository->add($order);
        $this->entityManager->flush();

        return $this->viewHandler->handle(View::create([]));
    }
}
