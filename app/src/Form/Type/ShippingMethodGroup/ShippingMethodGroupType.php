<?php

declare(strict_types=1);

namespace App\Form\Type\ShippingMethodGroup;

use App\Doctrine\Entity\ShippingMethodGroup;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShippingMethodGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'sylius.ui.code',
            ])
            ->add('maxPerDay', IntegerType::class, [
                'label' => 'sylius.ui.shipping.max_per_day',
                'required' => false,
            ])
            ->add('maxPerSlot', IntegerType::class, [
                'label' => 'sylius.ui.shipping.max_per_slot',
                'required' => false,
            ])
            ->add(
                'shippingMethods',
                CollectionType::class,
                [
                    'label' => false,
                    'entry_type' => EntityType::class,
                    'entry_options' => [
                        'class' => ShippingMethod::class,
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShippingMethodGroup::class,
        ]);
    }
}
