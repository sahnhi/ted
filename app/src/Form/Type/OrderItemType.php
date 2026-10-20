<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Form\Type\Reason\BisSupportType;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Sylius\AdminOrderCreationPlugin\Form\Type\AdjustmentType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceAutocompleteChoiceType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderItemType extends AbstractResourceType
{
    public function __construct(
        private readonly DataMapperInterface $dataMapper,
        array $validationGroups = []
    ) {
        parent::__construct(OrderItem::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /* Custom Code */
        if (isset($options['constraints']['order_type']) && 'return' === $options['constraints']['order_type']) {
            $wrapType = CheckboxType::class;
            $wrapData['empty_data'] = 0;
        } else {
            $wrapType = HiddenType::class;
            $wrapData['data'] = 1;
        }
        /* End Custom Code */

        $builder
            ->add('quantity', IntegerType::class, [
                'attr' => ['min' => 1],
                'label' => 'sylius.ui.quantity',
                'empty_data' => 1,
            ])
            ->add('variant', ResourceAutocompleteChoiceType::class, [
                'label' => 'sylius.ui.variant',
                'choice_name' => 'descriptor',
                'choice_value' => 'code',
                'resource' => 'sylius.product_variant',
            ]);

        /* Custom Code */
        if (isset($options['attr']) && isset($options['attr']['returnType']) && !in_array($options['attr']['returnType'], [ReturnTypeServiceInterface::RETURN_TYPE_SENDER, ReturnTypeServiceInterface::RETURN_TYPE_WAREHOUSE])) {
            $builder->add('orderReason', CollectionType::class, [
                'label' => false,
                'entry_type' => BisSupportType::class,
                'entry_options' => [
                    'user' => $options['user'],
                    'constraints' => $options['constraints'],
                    'attr' => $options['attr'],
                ],
                'by_reference' => false,
                'mapped' => false,
                'data' => [
                    new OrderReason(),
                ],
            ]);
        }
        $builder
            ->add('wrap', $wrapType, array_merge([
                'label' => 'sylius_admin_order_creation.ui.wrap',
                'required' => false,
                'mapped' => false,
            ], $wrapData))
            /* End Custom Code */

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                $event
                    ->getForm()
                    ->add('adjustments', CollectionType::class, [
                        'label' => false,
                        'entry_type' => AdjustmentType::class,
                        'entry_options' => [
                            'label' => 'sylius_admin_order_creation.ui.item_discount',
                            'currency' => $options['currency'],
                            'type' => AdjustmentType::ORDER_ITEM_DISCOUNT_ADJUSTMENT,
                        ],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'button_add_label' => 'sylius_admin_order_creation.ui.add_discount',
                    ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $data = $event->getData();
                if (empty($data['quantity'])) {
                    $data['quantity'] = '1';
                }
                if (!isset($data['wrap']) && !str_starts_with((string) $data['variant'], 'R')) {
                    $data['variant'] = 'R' . $data['variant'];
                }
                $event->setData($data);
            })
            ->setDataMapper($this->dataMapper);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('currency');

        $resolver->setDefaults([
            'user' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_order_creation_new_order_order_item';
    }
}
