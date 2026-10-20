<?php

namespace App\Form\Extension;

use Sylius\AdminOrderCreationPlugin\Form\Type\NewOrderCustomerCreateType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class NewOrderCustomerCreateTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerFirstName', TextType::class, [
                'label' => false,
            ])
            ->add('customerLastName', TextType::class, [
                'label' => false,
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [NewOrderCustomerCreateType::class];
    }
}
