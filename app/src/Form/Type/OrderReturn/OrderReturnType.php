<?php

namespace App\Form\Type\OrderReturn;

use App\Doctrine\Entity\OrderReturn;
use App\Doctrine\Entity\OrderReturnItem;
use App\Doctrine\Entity\OrderReturnStatus;
use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderReturnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $order = null;
        $variantIds = [];
        if (!empty($options['data'])) {
            $order = $options['data']->getOrder();
            if (!empty($order)) {
                $products = $order->getItems();
                foreach ($products as $product) {
                    $variantIds[] = $product->getVariant()->getId();
                }
            }
        }
        if (null !== $order) {
            $builder
                ->add('order', EntityType::class, [
                    'class' => Order::class,
                    'label' => 'sylius.ui.order_return.order',
                    'query_builder' => fn (EntityRepository $e) => $e->createQueryBuilder('s')
                        ->where('s.id = :orderId')
                        ->setParameter('orderId', $order),
                    'attr' => [
                        'readonly' => true,
                    ],
                ])
            ;
        }

        $builder
            ->add('variants', HiddenType::class)
            ->add('savStatus', EntityType::class, [
                'class' => OrderReturnStatus::class,
                'label' => 'sylius.form.order_return.savStatus',
                'placeholder' => 'sylius.form.order_return.sav_placeholder',
                'required' => false,
                'query_builder' => fn (EntityRepository $e) => $e->createQueryBuilder('s')
                    ->where('s.statusType = :type')
                    ->setParameter('type', OrderReturnStatus::TYPE_SAV),
                'choice_label' => 'savStatus',
                'choice_translation_domain' => 'order-return-status',
            ])
            ->add('warehouseStatus', EntityType::class, [
                'class' => OrderReturnStatus::class,
                'label' => 'sylius.form.order_return.warehouseStatus',
                'placeholder' => 'sylius.form.order_return.warehouse_placeholder',
                'required' => false,
                'query_builder' => fn (EntityRepository $e) => $e->createQueryBuilder('s')
                    ->where('s.statusType = :type')
                    ->setParameter('type', OrderReturnStatus::TYPE_WAREHOUSE),
                'choice_label' => 'warehouseStatus',
                'choice_translation_domain' => 'order-return-status',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'sylius.form.order_return.notes',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($variantIds, $order): void {
                $form = $event->getForm();
                $data = $event->getData();

                if (empty($data->getId()) && !empty($order) && !empty($order->getItems())) {
                    foreach ($order->getItems() as $item) {
                        $returnItem = new OrderReturnItem();
                        $returnItem->setQuantity($item->getQuantity());
                        $returnItem->setVariant($item->getVariant());
                        $data->addItem($returnItem);
                    }
                }
                $form->add('items', CollectionType::class, [
                    'entry_type' => VariantReturnType::class,
                    'label' => 'sylius.ui.order_return.product_variant',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'button_add_label' => 'sylius.ui.order_return.add_product',
                    'required' => false,
                    'entry_options' => [
                        'attr' => $variantIds,
                    ],
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                /** @var OrderReturn $orderReturnData */
                $orderReturnData = $event->getData();

                $variants = '';
                if (!empty($orderReturnData['items'])) {
                    foreach ($orderReturnData['items'] as $product) {
                        $variants .= $product['quantity'] . ' x ' . $product['variant'] . ', ';
                    }
                    $orderReturnData['variants'] = substr($variants, 0, strlen($variants) - 2);
                    $event->setData($orderReturnData);
                }

                if (!$event->getForm()->has('order') && !empty($orderReturnData['order'])) {
                    $event->getForm()
                        ->add('order', EntityType::class, [
                            'class' => Order::class,
                            'label' => 'sylius.ui.order_return.order',
                            'query_builder' => fn (EntityRepository $e) => $e->createQueryBuilder('s')
                                ->where('s.id = :orderId')
                                ->setParameter('orderId', $orderReturnData['order']),
                            'attr' => [
                                'readonly' => true,
                            ],
                        ])
                    ;
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderReturn::class,
            'allow_extra_fields' => true,
        ]);
    }
}
