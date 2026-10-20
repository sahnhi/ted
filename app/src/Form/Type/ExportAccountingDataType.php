<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class ExportAccountingDataType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'app.ui.export_accounting_data.start_date',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'app.ui.export_accounting_data.end_date',
            ])
            ->add('scope', ChoiceType::class, [
                'choices' => [
                    'app.ui.export_accounting_data.scope_checkout' => 'checkout',
                    'app.ui.export_accounting_data.scope_delivered' => 'delivered',
                ],
                'label' => 'app.ui.export_accounting_data.scope',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_export_accounting_data';
    }
}
