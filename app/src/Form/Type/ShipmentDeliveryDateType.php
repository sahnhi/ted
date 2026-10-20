<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Sylius\Shipment;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ShipmentDeliveryDateType extends AbstractResourceType
{
    public function __construct()
    {
        parent::__construct(Shipment::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDeliverySlot', DateTimeType::class, [
                'label' => 'app.ui.start_delivery_slot',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'allowNull' => false,
                        'message' => 'app.shipment_delivery_date.start_delivery_slot.not_blank',
                        'groups' => ['sylius'],
                    ]),
                    new DateTime([
                        'message' => 'app.shipment_delivery_date.start_delivery_slot.date_time',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('endDeliverySlot', DateTimeType::class, [
                'label' => 'app.ui.end_delivery_slot',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'allowNull' => false,
                        'message' => 'app.shipment_delivery_date.end_delivery_slot.not_blank',
                        'groups' => ['sylius'],
                    ]),
                    new DateTime([
                        'message' => 'app.shipment_delivery_date.end_delivery_slot.date_time',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('notifyWarehouse', CheckboxType::class, [
                'label' => 'app.ui.shipment.form.notify_warehouse',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_shipment_delivery_date';
    }
}
