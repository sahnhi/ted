<?php

namespace App\Form\Type;

use App\Doctrine\Entity\ServiceRules;
use App\Doctrine\Entity\Sylius\ShippingCategory;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceRulesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shippingMethod', EntityType::class, [
                'class' => ShippingMethod::class,
                'label' => 'sylius.ui.service_rule.shipping_method',
            ])
            ->add('name', TextType::class, [
                'label' => 'sylius.ui.service_rule.name',
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'sylius.ui.service_rule.priority',
            ])
            ->add('shippingCategory', EntityType::class, [
                'class' => ShippingCategory::class,
                'multiple' => true,
                'label' => 'sylius.menu.admin.shipping_category',
                'required' => false,
                'by_reference' => false,
                'expanded' => true,
            ])
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'label' => 'sylius.ui.service_rule.warehouse',
            ])
            ->add('deliveryZone', EntityType::class, [
                'class' => Zone::class,
                'required' => false,
                'label' => 'sylius.ui.service_rule.zone',
            ])
            ->add('deliverySlot', TimeType::class, [
                'attr' => [
                    'data-time' => 'true',
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.service_rule.delivery_slot',
                'required' => false,
            ])
            ->add('transportTime', NumberType::class, [
                'attr' => [
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.shipping.transport_time',
                'required' => false,
            ])
            ->add('exceptionalSchedule', CollectionType::class, [
                'label' => 'sylius.ui.exceptional_schedule.label',
                'entry_type' => ExceptionalScheduleType::class,
                'entry_options' => [
                    'with_extra_fields' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceRules::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_service_rules';
    }
}
