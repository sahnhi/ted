<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\Warehouse;
use App\Enum\FtpConfigurationName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius.ui.shipping_stock.warehouse',
            ])
            ->add('ftpConfigurationName', ChoiceType::class, [
                'label' => 'sylius.ui.ftp_details.label',
                'choices' => FtpConfigurationName::cases(),
                'choice_label' => 'value',
                'choice_value' => 'value',
                'required' => false,
                'placeholder' => 'sylius.ui.ftp_details.placeholder',
            ])
            ->add('crmName', TextType::class, [
                'label' => 'sylius.form.warehouse.crm',
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
                'label' => 'sylius.ui.warehouse.calendar_days',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('openingTime', TimeType::class, [
                'attr' => [
                    'data-time' => 'true',
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.warehouse.opening_time',
                'required' => false,
            ])
            ->add('closingTime', TimeType::class, [
                'attr' => [
                    'data-time' => 'true',
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.warehouse.closing_time',
                'required' => false,
            ])
            ->add('cutOff', TimeType::class, [
                'attr' => [
                    'data-time' => 'true',
                    'style' => 'display:inline-flex',
                ],
                'label' => 'sylius.ui.warehouse.cutoff',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Warehouse::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_warehouse';
    }
}
