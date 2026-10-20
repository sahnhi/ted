<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Form\Type\ShipmentType;
use App\Form\Type\ShippingMethodCategory\ShippingMethodCategoryChoiceType;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\SelectShippingType;
use Sylius\Component\Core\Repository\AddressRepositoryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class SelectShippingTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly ShippingMethodCategoryProvider $shippingMethodCategoryProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Order $order */
        $order = $builder->getData();

        $builder
            ->add('shippingMethodCategory', ShippingMethodCategoryChoiceType::class, [
                'label' => 'app.ui.shipping_method_categories',
                'required' => true,
                'mapped' => false,
                'shipping_method_categories' => $this->shippingMethodCategoryProvider->provideEligibleForOrder($order),
                'order' => $order,
            ]);

        $builder->add('shipments', CollectionType::class, [
            'entry_type' => ShipmentType::class,
            'label' => 'sylius.ui.shipments',
            'by_reference' => false,
            'attr' => ['class' => 'shipment-form'],
        ])
        ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var Order $order */
            $order = $event->getData();
            /** @var Address|null $address */
            $address = $this->addressRepository->find($order->getShippingAddress()->getId());

            $customer = $order->getCustomer();
            $customer->setPhoneNumber($address->getPhoneNumber());
            $customer->setFirstName($address->getFirstName());
            $customer->setLastName($address->getLastName());
            $order->setShippingAddress($address);
            $event->setData($order);
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [SelectShippingType::class];
    }
}
