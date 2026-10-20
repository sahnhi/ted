<?php

namespace App\Form\Type\Payment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BraintreeGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('merchantId', TextType::class)
            ->add('privateKey', TextType::class)
            ->add('publicKey', TextType::class)
            ->add('sandbox', CheckboxType::class)
        ;
    }
}
