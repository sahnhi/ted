<?php

namespace App\Handler;

use App\Business\Command\AddressOrder;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Customer;
use App\Model\Address as AddressDTO;
use App\Validator\Constraints\CustomerEmail;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Factory\AddressFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class AddressOrderHandler
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly AddressFactoryInterface $addressFactory,
        private readonly Factory $customerFactory,
        private readonly FactoryInterface $stateMachineFactory,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(AddressOrder $addressOrder): OrderInterface
    {
        $order = $addressOrder->order();
        $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);
        Assert::true(
            $stateMachine->can(OrderCheckoutTransitions::TRANSITION_ADDRESS),
            $this->translator->trans('app.api.order.address.error.cannot_complete', ['token' => $addressOrder->orderToken()])
        );

        $customDto = $addressOrder->customer();

        /** @var Customer|null $customer */
        $customer = $this->customerRepository->findOneBy(['email' => $customDto->email()]);

        if (!$customer) {
            /** @var Customer $customer */
            $customer = $this->customerFactory->createNew();
        }
        $orderLocale = explode('_', (string) $order->getLocaleCode());
        $defaultLocale = $orderLocale[1] ?? '';

        $customerEmailValidate = new CustomerEmail();
        $errors = $this->validator->validate($customDto->email(), $customerEmailValidate);
        Assert::count($errors, 0, $this->translator->trans('app.api.order.address.error.email'));

        $customer->setEmail($customDto->email());
        $customer->setPhoneNumber($customDto->phoneNumber());
        $customer->setFirstName($customDto->firstName());
        $customer->setLastName($customDto->lastName());
        $customer->setSubscribedToNewsletter($customDto->newsletter());
        $customer->setOptin($customDto->optin());
        $customer->setOptinPhone($customDto->optinPhone());
        $customer->setLocale($order->getLocaleCode());

        $shippingAddress = $this->createNewAddressFromDto($addressOrder->shippingAddress(), $customer, $defaultLocale);
        $billingAddress = $this->createNewAddressFromDto($addressOrder->billingAddress(), $customer, $defaultLocale);
        $customer->setDefaultAddress($shippingAddress);

        $order->setShippingAddress($shippingAddress);
        $order->setBillingAddress($billingAddress);
        $order->setCustomer($customer);

        $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_ADDRESS);

        return $order;
    }

    public function createNewAddressFromDto(AddressDTO $addressDto, Customer $customer, string $defaultLocale): Address
    {
        /** @var Address $address */
        $address = $this->addressFactory->createNew();

        $address->setFirstName($addressDto->getFirstName());
        $address->setLastName($addressDto->getLastName());
        $address->setCity($addressDto->getCity());
        $address->setStreet($addressDto->getStreet());
        $address->setCountryCode(!empty($addressDto->getCountryCode()) && ' ' !== $addressDto->getCountryCode() ? $addressDto->getCountryCode() : $defaultLocale);
        $address->setPostcode($addressDto->getPostcode());
        $address->setProvinceName($addressDto->getProvinceName());
        $address->setProvinceCode($addressDto->getProvinceCode());
        $address->setDetails($addressDto->getDetails());
        $address->setTva($addressDto->getTva());
        $address->setCompany($addressDto->getCompany());
        $address->setCustomer($customer);
        $address->setPhoneNumber($customer->getPhoneNumber());

        return $address;
    }
}
