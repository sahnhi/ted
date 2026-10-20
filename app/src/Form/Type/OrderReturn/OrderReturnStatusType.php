<?php

namespace App\Form\Type\OrderReturn;

use App\Doctrine\Entity\OrderReturnStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderReturnStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('statusType', ChoiceType::class, [
                'choices' => [
                    'sylius.form.order_return_status.type.sav' => OrderReturnStatus::TYPE_SAV,
                    'sylius.form.order_return_status.type.warehouse' => OrderReturnStatus::TYPE_WAREHOUSE,
                ],
                'label' => 'sylius.form.order_return_status.type.label',
            ])
            ->add('savStatus', TextType::class, [
                'label' => 'sylius.form.order_return_status.sav_status',
            ])
            ->add('warehouseStatus', TextType::class, [
                'label' => 'sylius.form.order_return_status.warehouse_status',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => OrderReturnStatus::class]);
    }
}
