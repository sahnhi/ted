<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\Sylius\Order;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NewRefundType extends AbstractResourceType
{
    public function __construct(array $validationGroups = [])
    {
        parent::__construct(Order::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!empty($options['attr']['returnType'])) {
            $builder->add('returnType', HiddenType::class, [
                'mapped' => false,
                'data' => $options['attr']['returnType'],
            ]);
        }

        $builder
            ->add('bis', HiddenType::class)
            ->add('return', HiddenType::class)
            ->add('parentNumber', HiddenType::class)
            ->add('shippingAddress', AddressType::class, [
                'label' => 'sylius.ui.shipping_address',
            ])
            ->add('billingAddress', AddressType::class, [
                'label' => 'sylius.ui.billing_address',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                /** @var OrderInterface $order */
                $order = $event->getData();

                /** @var ChannelInterface $channel */
                $channel = $order->getChannel();
                $form = $event->getForm();
                $form
                    ->add('items', CollectionType::class, [
                        'label' => 'sylius.ui.items',
                        'entry_type' => OrderItemType::class,
                        'entry_options' => [
                            'currency' => $order->getCurrencyCode(),
                            'constraints' => $options['constraints'],
                            'user' => $options['user'],
                            'attr' => $options['attr'],
                        ],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                    ])
                ;
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $orderData = $event->getData();

                if (isset($orderData['shippingAddress']) && $this->isBillingAddressEmpty($orderData)) {
                    $orderData['billingAddress'] = $orderData['shippingAddress'];

                    $event->setData($orderData);
                }
            })
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_order_creation_new_order';
    }

    private function isBillingAddressEmpty(array $orderData): bool
    {
        if (!isset($orderData['billingAddress'])) {
            return true;
        }

        return
            '' === $orderData['billingAddress']['firstName']
            && '' === $orderData['billingAddress']['lastName']
            && '' === $orderData['billingAddress']['street']
            && '' === $orderData['billingAddress']['countryCode']
            && '' === $orderData['billingAddress']['city']
            && '' === $orderData['billingAddress']['postcode']
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['user' => null]);
    }
}
