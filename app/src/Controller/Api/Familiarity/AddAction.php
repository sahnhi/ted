<?php

namespace App\Controller\Api\Familiarity;

use App\Doctrine\Entity\Friend;
use App\Doctrine\Entity\Osteopath;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\ViewRepository\CartViewRepositoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

class AddAction
{
    public function __construct(
        private readonly OrderRepository $cartRepository,
        private readonly EntityRepository $familiarityRepository,
        private readonly EntityRepository $customerRepository,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $token = $request->attributes->get('token');
        $familiaritiesRequest = $request->request->get('familiarities');
        $osteoRequest = $request->request->get('osteo');
        $friendRequest = $request->request->get('friend');

        $cart = $this->cartRepository->findOneBy(['tokenValue' => $token]);
        $customer = $cart->getCustomer();

        Assert::notNull($cart, 'Cart with given id does not exists');
        Assert::notNull($customer, 'Cart with no customer');

        if ($osteoRequest) {
            $osteo = new Osteopath();
            $osteo->setName($osteoRequest);
            $osteo->setAddress($cart->getShippingAddress());
            $customer->setOsteopath($osteo);
        }

        if ($friendRequest) {
            $friend = new Friend();
            $friend->setName($friendRequest);
            $customer->setFriend($friend);
        }

        foreach ($familiaritiesRequest as $familiarityCode) {
            $familiarity = $this->familiarityRepository->findOneBy(['code' => $familiarityCode]);

            if ($familiarity) {
                $customer->addFamiliarity($familiarity);
            }
        }

        $this->customerRepository->add($customer);

        try {
            return $this->viewHandler->handle(
                View::create($this->cartQuery->getOneByToken($token), Response::HTTP_OK)
            );
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
