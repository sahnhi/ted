<?php

namespace App\Form\Type;

use App\Doctrine\Entity\ExceptionalSchedule;
use App\Doctrine\Entity\ServiceRules;
use App\Doctrine\Entity\ShippingMethodGroup;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExceptionalScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // When used in other formTypes, we sometimes do not want the extra shippingMethodGroup, shippingMethod
        // and serviceRules fields, since these values will be set on the parent form during submit.
        if (true === $options['with_extra_fields']) {
            $data = $builder->getData();
            if ($data instanceof ExceptionalSchedule && null === $data->getId()) {
                $builder
                    ->add('shippingMethodGroup', EntityType::class, [
                        'class' => ShippingMethodGroup::class,
                        'mapped' => false,
                        'label' => 'app.ui.shipping_method_groups.admin_menu',
                        'placeholder' => 'app.ui.shipping_method_groups.placeholder',
                        'choice_label' => 'code',
                    ]);
            }

            $builder
                ->add('shippingMethod', EntityType::class, [
                    'class' => ShippingMethod::class,
                    'label' => 'sylius.ui.shipping_method',
                    'placeholder' => 'sylius.form.shipping_method.placeholder',
                ])
                ->add('serviceRules', EntityType::class, [
                    'class' => ServiceRules::class,
                    'label' => 'sylius.ui.service_rule.title',
                    'placeholder' => 'sylius.ui.service_rule.placeholder',
                ]);
        }

        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'sylius.ui.exceptional_schedule.type',
                'choices' => [
                    'sylius.ui.exceptional_schedule.opening' => ExceptionalSchedule::EXCEPTIONAL_TYPE[0],
                    'sylius.ui.exceptional_schedule.closing' => ExceptionalSchedule::EXCEPTIONAL_TYPE[1],
                    'sylius.ui.exceptional_schedule.opening_slot' => ExceptionalSchedule::EXCEPTIONAL_TYPE[2],
                    'sylius.ui.exceptional_schedule.closing_slot' => ExceptionalSchedule::EXCEPTIONAL_TYPE[3],
                ],
            ])
            ->add('calendarDays', ChoiceType::class, [
                'choices' => [
                    'sylius.ui.days.monday' => 1,
                    'sylius.ui.days.tuesday' => 2,
                    'sylius.ui.days.wednesday' => 3,
                    'sylius.ui.days.thursday' => 4,
                    'sylius.ui.days.friday' => 5,
                    'sylius.ui.days.saturday' => 6,
                    'sylius.ui.days.sunday' => 7,
                ],
                'label' => 'sylius.ui.exceptional_schedule.calendar_days',
                'multiple' => true,
                'required' => false,
            ])
            ->add('start', DateTimeType::class, [
                'date_widget' => 'single_text',
                'label' => 'sylius.ui.exceptional_schedule.start',
                'attr' => [
                    'data-end-slot' => 'true',
                ],
                'time_widget' => 'single_text',
                'required' => false,
            ])
            ->add('end', TimeType::class, [
                'label' => 'sylius.ui.exceptional_schedule.end',
                'attr' => [
                    'data-end-slot' => 'true',
                ],
                'widget' => 'single_text',
                'required' => false,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $data = $event->getData();

                if (null !== $data->getStart() && null !== $data->getEnd()) {
                    $end = clone $data->getStart();
                    $end->setTime($data->getEnd()->format('H'), $data->getEnd()->format('i'));
                    $data->setEnd($end);
                }

                $event->setData($data);
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ExceptionalSchedule::class,
                'with_extra_fields' => true,
            ])
            ->setAllowedTypes('with_extra_fields', ['bool']);
    }
}
