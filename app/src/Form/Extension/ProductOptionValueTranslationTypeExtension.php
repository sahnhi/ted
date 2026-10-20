<?php

namespace App\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionValueTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductOptionValueTranslationTypeExtension extends AbstractTypeExtension
{
    public const CHOICES_VALUES = [
        'Non standard' => 0,
        'Standard' => 1,
        'Jamini' => 9,
        'Milk with Mint' => 95,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('value')
            ->add('optgroup', ChoiceType::class, [
                'label' => 'sylius.form.option_value.optgroup',
                'choices' => self::CHOICES_VALUES,
            ])
            ->add('value', TextType::class, [
                'label' => 'sylius.form.option_value.value',
            ])
            ->add('help', TextareaType::class, [
                'label' => 'sylius.form.option_value.help',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'sylius.form.option_value.description',
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductOptionValueTranslationType::class];
    }
}
