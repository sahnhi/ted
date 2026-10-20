<?php

namespace App\Form\Extension\Payment;

use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class PaymentMethodTypeExtension extends AbstractTypeExtension
{
    use ExtendedPaymentMethodTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->appendToForm($builder, $options);
    }

    public static function getExtendedTypes(): iterable
    {
        return [PaymentMethodType::class];
    }
}
