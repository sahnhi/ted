<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\ShippingMethodGroup;
use App\Doctrine\Entity\Sylius\ShippingCategory;
use App\Doctrine\Entity\Warehouse;
use App\Form\Type\ExceptionalScheduleType;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ShippingMethodTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('deliveryMessage', TextareaType::class, [
                'label' => 'sylius.ui.shipping.delivery_message',
                'required' => false,
            ])
            ->add('openingCalendarDays', ChoiceType::class, [
                'choices' => [
                    'sylius.ui.days.monday' => 1,
                    'sylius.ui.days.tuesday' => 2,
                    'sylius.ui.days.wednesday' => 3,
                    'sylius.ui.days.thursday' => 4,
                    'sylius.ui.days.friday' => 5,
                    'sylius.ui.days.saturday' => 6,
                    'sylius.ui.days.sunday' => 7,
                ],
                'label' => 'sylius.ui.shipping.opening_calendar_days',
                'multiple' => true,
            ])
            ->add('openingTime', TimeType::class, [
                'attr' => [
                    'data-time' => 'true',
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.shipping.opening_time',
                'required' => false,
            ])
            ->add('closingTime', TimeType::class, [
                'attr' => [
                    'data-time' => 'true',
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.shipping.closing_time',
                'required' => false,
            ])
            ->add('maxPerDay', TextType::class, [
                'label' => 'sylius.ui.shipping.max_per_day',
                'required' => false,
            ])
            ->add('maxPerSlot', TextType::class, [
                'label' => 'sylius.ui.shipping.max_per_slot',
                'required' => false,
            ])
            ->add('maxArticles', NumberType::class, [
                'label' => 'sylius.ui.shipping.max_articles',
                'required' => false,
            ])
            ->add('timeSlot', TimeType::class, [
                'attr' => [
                    'data-time' => 'true',
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.shipping.time_slot',
                'required' => false,
            ])
            ->add('shippingMethodGroup', EntityType::class, [
                'class' => ShippingMethodGroup::class,
                'label' => 'sylius.menu.admin.shipping_method_group',
                'required' => false,
            ])
            ->add('shippingCategory', EntityType::class, [
                'class' => ShippingCategory::class,
                'multiple' => true,
                'label' => 'sylius.menu.admin.shipping_category',
                'required' => false,
                'by_reference' => false,
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
            ])
            ->add('shippingTimeDate', NumberType::class, [
                'attr' => [
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.shipping.shipping_time_date',
                'required' => false,
            ])
            ->add('floor', CheckboxType::class, [
                'label' => 'sylius.form.shipping_method.floor',
                'required' => false,
            ])
            ->add('tuned', CheckboxType::class, [
                'label' => 'sylius.form.shipping_method.tuned',
                'required' => false,
            ])
            ->add('hasTracking', CheckboxType::class, [
                'label' => 'sylius.form.shipping_method.tracking',
                'required' => false,
            ])
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'label' => 'sylius.ui.warehouse.title',
                'required' => true,
                'placeholder' => 'sylius.ui.warehouse.placeholder',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('calendar', CheckboxType::class, [
                'label' => 'sylius.form.shipping_method.calendar',
                'required' => false,
            ])
            ->add('PickUpPoint', CheckboxType::class, [
                'label' => 'sylius.form.shipping_method.pick_up_point',
                'required' => false,
            ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodType::class];
    }
}
