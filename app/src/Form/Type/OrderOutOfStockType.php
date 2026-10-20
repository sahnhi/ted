<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\OrderOutOfStock;
use App\Doctrine\Entity\OrderOutOfStockInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderOutOfStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('customerState', ChoiceType::class, [
            'label' => 'sylius.form.order.out_of_stock.customer_state',
            'empty_data' => null,
            'required' => false,
            'placeholder' => 'sylius.form.order.out_of_stock.empty_choice',
            'choices' => [
                'sylius.ui.called_no' => OrderOutOfStock::OUTOFSTOCK_CUSTOMER_STATE_CALLED_NO,
                'sylius.ui.called' => OrderOutOfStock::OUTOFSTOCK_CUSTOMER_STATE_CALLED,
                'sylius.ui.need_call' => OrderOutOfStock::OUTOFSTOCK_CUSTOMER_STATE_NEED_CALL,
                'sylius.ui.calledback' => OrderOutOfStock::OUTOFSTOCK_CUSTOMER_STATE_CALLEDBACK,
            ],
        ])->add('status', ChoiceType::class, [
            'label' => 'sylius.ui.order_outofstocks_state',
            'choices' => [
                'sylius.ui.partially_planned' => OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED,
                'sylius.ui.out_of_stock' => OrderOutOfStockInterface::STATE_OUT_OF_STOCK,
                'sylius.ui.to_send' => OrderOutOfStockInterface::STATE_TO_SEND,
            ],
        ])->add('deliverDate', DateType::class, [
            'widget' => 'single_text',
            'label' => 'sylius.ui.shipping_stock.date',
            'required' => false,
        ])->add('dateConfirmed', CheckboxType::class, [
            'required' => false,
            'label' => 'sylius.ui.order_outofstocks_date_confirmed',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_out_of_stock';
    }
}
