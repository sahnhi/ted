<?php

namespace App\Controller\Admin;

use App\Provider\CustomerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

final class CustomerCreationAction
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly CustomerProvider $customerProvider,
        private readonly EntityManagerInterface $customerManager
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->customerProvider->provideNewCustomer($request->attributes->get('customerEmail'));

        $customer->setFirstName($request->attributes->get('customerFirstName'));
        $customer->setLastName($request->attributes->get('customerLastName'));

        $this->customerManager->persist($customer);
        $this->customerManager->flush();

        return new RedirectResponse(
            $this->router->generate(
                'sylius_admin_order_creation_order_create',
                [
                    'customerId' => $customer->getId(),
                    'channelCode' => $request->attributes->get('channelCode'),
                ]
            )
        );
    }
}
