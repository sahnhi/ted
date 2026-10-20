<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MetaDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shippingMethod', ChoiceType::class, [
                'choices' => ShippingMethodInterface::META_DATA_METHOD,
                'label' => 'sylius.ui.product.metadata.shipping_method',
            ])
            ->add('data', TextType::class, [
                'label' => 'sylius.ui.product.metadata.data',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'product_meta_data';
    }
}
