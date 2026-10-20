<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\OrderOutOfStockInterface;
use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Form\Type\Order\BisExchangeOrderItemType;
use App\Form\Type\Order\BisExchangeType;
use App\Form\Type\Reason\BisSupportType;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\AdminOrderCreationPlugin\Form\Type\AdjustmentType;
use Sylius\AdminOrderCreationPlugin\Form\Type\CurrencyCodeChoiceType;
use Sylius\AdminOrderCreationPlugin\Form\Type\LocaleCodeChoiceType;
use Sylius\AdminOrderCreationPlugin\Form\Type\PaymentType;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\PromotionBundle\Form\Type\PromotionCouponToCodeType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NewOrderType extends AbstractResourceType
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        array $validationGroups = []
    ) {
        parent::__construct(Order::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!empty($options['attr']['returnType'])) {
            $builder->add('returnType', HiddenType::class, [
                'mapped' => false,
                'data' => $options['attr']['returnType'],
            ]);
            if (in_array($options['attr']['returnType'], [ReturnTypeServiceInterface::RETURN_TYPE_SENDER, ReturnTypeServiceInterface::RETURN_TYPE_WAREHOUSE])) {
                $builder->add('q77Notify', CheckboxType::class, [
                    'mapped' => false,
                    'data' => true,
                    'required' => false,
                    'label' => 'sylius.form.return.q77_notify',
                ]);
            }
        } elseif (!empty($options['constraints']['order_type'])) {
            $builder->add('bisType', HiddenType::class, [
                'mapped' => false,
                'data' => $options['constraints']['order_type'],
            ]);
        }

        $builder
            ->add('bis', HiddenType::class)
            ->add('return', HiddenType::class)
            ->add('parentNumber', HiddenType::class)
            ->add('promotionCoupon', PromotionCouponToCodeType::class, [
                'by_reference' => false,
                'label' => 'sylius.form.cart.coupon',
                'required' => false,
            ])
            ->add('shippingAddress', AddressType::class, [
                'label' => 'sylius.ui.shipping_address',
            ])
            ->add('billingAddress', AddressType::class, [
                'label' => 'sylius.ui.billing_address',
                'required' => false,
            ])
            ->add('payments', CollectionType::class, [
                'entry_type' => PaymentType::class,
                'label' => 'sylius.ui.payments',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('outOfStockToggler', CheckboxType::class, [
                'mapped' => false,
                'label' => 'sylius.form.order.stock_toggler',
                'required' => false,
                'data' => false,
            ])
            ->add('bisExchange', CollectionType::class, [
                'entry_type' => BisExchangeType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'mapped' => false,
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
                    ->add('bisItems', CollectionType::class, [
                        'label' => 'sylius.ui.items',
                        'entry_type' => BisExchangeOrderItemType::class,
                        'entry_options' => [
                            'currency' => $order->getCurrencyCode(),
                        ],
                        'data' => $order->getItems(),
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'mapped' => false,
                    ])
                    ->add('adjustments', CollectionType::class, [
                        'label' => false,
                        'entry_type' => AdjustmentType::class,
                        'entry_options' => [
                            'label' => 'sylius_admin_order_creation.ui.order_discount',
                            'currency' => $order->getCurrencyCode(),
                            'type' => AdjustmentType::ORDER_DISCOUNT_ADJUSTMENT,
                        ],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'button_add_label' => 'sylius_admin_order_creation.ui.add_discount',
                    ])
                    ->add('localeCode', LocaleCodeChoiceType::class, [
                        'label' => false,
                        'choices' => $channel->getLocales(),
                        'empty_data' => $order->getLocaleCode(),
                    ])
                    ->add('currencyCode', CurrencyCodeChoiceType::class, [
                        'label' => false,
                        'choices' => $channel->getCurrencies(),
                        'empty_data' => $order->getCurrencyCode(),
                    ])
                    ->add('payments', CollectionType::class, [
                        'entry_type' => PaymentType::class,
                        'label' => 'sylius.ui.payments',
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'entry_options' => [
                            'channel' => $channel,
                        ],
                    ])
                    ->add('shipments', CollectionType::class, [
                        'entry_type' => ShipmentType::class,
                        'label' => 'sylius.ui.shipments',
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'entry_options' => [
                            'channel' => $channel,
                        ],
                    ]);

                $form->add('orderReason', CollectionType::class, [
                    'label' => false,
                    'entry_type' => BisSupportType::class,
                    'entry_options' => [
                        'user' => $options['user'],
                        'constraints' => $options['constraints'],
                        'attr' => $options['attr'],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'data' => [
                        new OrderReason(),
                    ],
                ]);

                if (ChannelCode::CHANNEL_BIS === $order->getChannel()->getCode()) {
                    $form->add('parentState', ChoiceType::class, [
                        'label' => 'sylius.form.order.parent.label',
                        'choices' => [
                            'sylius.form.order.parent.out_of_stock' => OrderOutOfStockInterface::STATE_SOLVED,
                            'sylius.form.order.parent.cancel' => OrderInterface::STATE_CANCELLED,
                        ],
                        'placeholder' => 'sylius.form.order.parent.placeholder',
                        'required' => false,
                        'mapped' => false,
                    ]);
                    $form->add('parentId', HiddenType::class, [
                        'mapped' => false,
                        'data' => !empty($options['constraints']['order_parent_id']) ? $options['constraints']['order_parent_id'] : '',
                    ]);
                }

                if (empty($options['attr']['returnType'])) {
                    $form->add('outOfStock', ChoiceType::class, [
                        'label' => 'sylius.form.order.out_of_stock.label',
                        'choices' => [
                            'sylius.form.order.out_of_stock.partially_planned' => OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED,
                        ],
                        'placeholder' => 'sylius.form.order.out_of_stock.placeholder',
                        'required' => true,
                        'mapped' => false,
                    ]);
                }
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $orderData = $event->getData();

                if (isset($orderData['shippingAddress']) && $this->isBillingAddressEmpty($orderData)) {
                    $orderData['billingAddress'] = $orderData['shippingAddress'];

                    $event->setData($orderData);
                }

                $parentOrder = $orderData['parentNumber'];
                if (!empty($parentOrder)) {
                    $normalizeFormData = $event->getForm()->getNormData();
                    if (null !== $normalizeFormData->getChannel() && null !== $normalizeFormData->getChannel()->getCode() && ChannelCode::CHANNEL_RETURN === $normalizeFormData->getChannel()->getCode()) {
                        $initialOrder = $this->orderRepository->findInitialOrder($parentOrder);

                        if ($initialOrder->getShipments()->first() && in_array($initialOrder->getShipments()->first()->getMethod()->getCode(), ShippingMethodInterface::METHOD_RELAY) && $orderData['shippingAddress'] !== $orderData['billingAddress']) {
                            $event->getForm()->addError(new FormError('Attention l’adresse de livraison ne correspond pas à l’adresse de facturation du client.'));
                        }
                    }
                }

                if (!empty($orderData['items'])) {
                    foreach ($orderData['items'] as $key => $item) {
                        if (!empty($item['orderReason'])) {
                            $first = array_key_first($item['orderReason']);
                            $orderReason = $item['orderReason'][$first];
                            $orderData['orderReason'][$key] = $orderReason;
                            $event->setData($orderData);
                        }
                    }
                }
            })
            ->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
                /** @var Order $order */
                $order = $event->getData();
                if (!$order->getItems()->isEmpty()) {
                    $items = new ArrayCollection($order->getItems()->getValues());
                    $order->clearItems();

                    foreach ($items as $item) {
                        $order->addItem($item);
                    }
                }
                if ($order->getOrderReason()->isEmpty() && ChannelCode::CHANNEL_BIS === $order->getChannel()->getCode()) {
                    $order->addOrderReason(new OrderReason());
                    $event->setData($order);
                }
                if ($order->getShipments()->first()
                    && in_array($order->getShipments()->first()->getMethod()->getCode(), ShippingMethodInterface::METHOD_RELAY)
                    && !empty($order->getBillingAddress())) {
                    $order->setShippingAddress($order->getBillingAddress());
                    $event->setData($order);
                }
            });
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
            && '' === $orderData['billingAddress']['postcode'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null,
            'channel' => null,
        ]);
    }
}
