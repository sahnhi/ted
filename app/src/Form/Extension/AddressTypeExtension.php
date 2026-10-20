<?php

namespace App\Form\Extension;

use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class AddressTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('details', TextareaType::class, [
            'required' => false,
        ])
        ->add('phoneNumber', TextType::class, [
            'required' => true,
            'label' => 'sylius.form.address.phone_number',
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [AddressType::class];
    }
}
