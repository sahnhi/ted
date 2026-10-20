<?php

namespace App\Form\Extension\Payment;

use App\Form\Type\Payment\PaymentMethodRuleType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

trait ExtendedPaymentMethodTrait
{
    public function appendToForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rules', CollectionType::class, [
                'label' => 'app.form.payment_method.rules.label',
                'entry_type' => PaymentMethodRuleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }
}
