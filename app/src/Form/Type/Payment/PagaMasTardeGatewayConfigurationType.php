<?php

namespace App\Form\Type\Payment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class PagaMasTardeGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('okUrl', UrlType::class)
            ->add('nokUrl', UrlType::class)
            ->add('cancelUrl', UrlType::class)
            ->add('callbackUrl', UrlType::class)
            ->add('secretKey', TextType::class)
            ->add('publicKey', TextType::class)
            ->add('sandbox', CheckboxType::class)
        ;
    }
}
