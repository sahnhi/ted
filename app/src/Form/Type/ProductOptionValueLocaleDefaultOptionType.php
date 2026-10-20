<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Sylius\ProductOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductOptionValueLocaleDefaultOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('option', ChoiceType::class, [
                'choices' => $options['attr']['product']->getOptions(),
                'choice_label' => fn ($option) => $option->getName(),
                'choice_value' => fn (?ProductOption $entity = null) => $entity ? $entity->getId() : '',
                'mapped' => false,
            ])
            ->add('locale', CollectionType::class, [
                'entry_type' => ProductOptionValueLocaleDefaultLocaleType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => 'product-option-locale-default-box',
                        'product_option' => $options['attr']['product']->getOptions(),
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => false,
                'mapped' => false,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'sylius_product_option_locale_default_option';
    }
}
