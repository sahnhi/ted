<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductOptionValueLocaleDefaultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('optionsLocalesDefault', CollectionType::class, ['entry_type' => ProductOptionValueLocaleDefaultOptionType::class, 'entry_options' => [
            'attr' => [
                'class' => 'product-option-locale-default-box',
                'product' => $builder->getData(),
            ],
        ], 'allow_add' => true, 'allow_delete' => false, 'mapped' => false, ]);
    }

    public function getBlockPrefix()
    {
        return 'sylius_product_option_locale_default';
    }
}
