<?php

namespace App\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionValueType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductOptionTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('values', CollectionType::class, [
                'entry_type' => ProductOptionValueType::class,
                'allow_add' => true,
                'by_reference' => false,
                'label' => false,
                'button_add_label' => 'sylius.form.option_value.add_value',
                'allow_delete' => true,
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductOptionType::class];
    }
}
