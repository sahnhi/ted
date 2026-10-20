<?php

namespace App\Handler;

use App\Business\Command\GiftCardAddressOrder;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Operator\GiftCard\OrderGiftCardOperator;
use App\Validator\Constraints\CustomerEmail;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class GiftCardAddressOrderHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly Factory $customerFactory,
        private readonly OrderGiftCardOperator $operatorOrderGiftCard,
        private readonly FactoryInterface $stateMachineFactory,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(GiftCardAddressOrder $giftCardAddressOrder): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $giftCardAddressOrder->orderToken()]);
        Assert::notNull($order, sprintf('Order with %s token has not been found.', $giftCardAddressOrder->orderToken()));

        $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);
        Assert::true($stateMachine->can(OrderCheckoutTransitions::TRANSITION_ADDRESS), sprintf('Order with %s token cannot be addressed.', $giftCardAddressOrder->orderToken()));

        $customer = $this->customerRepository->findOneBy(['email' => $giftCardAddressOrder->giftCardAddress()->emailBuyer()]);

        if (!$customer) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
        }

        $customerEmailValidate = new CustomerEmail();
        $errors = $this->validator->validate($giftCardAddressOrder->giftCardAddress()->emailBuyer(), $customerEmailValidate);
        Assert::count($errors, 0, "Cet e-mail n'est pas valide");

        $customer->setEmail($giftCardAddressOrder->giftCardAddress()->emailBuyer());

        $order->setCustomer($customer);

        $this->operatorOrderGiftCard->create($order, $giftCardAddressOrder->giftCardAddress());

        $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_ADDRESS);
    }
}
