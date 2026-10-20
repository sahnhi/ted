<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Sylius\ProductOptionValue;
use Sylius\Component\Locale\Model\Locale;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValueLocaleDefaultLocaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $optionValueChoices = [];
        foreach ($options['attr']['product_option'] as $option) {
            foreach ($option->getValues() as $value) {
                $optionValueChoices[] = $value;
            }
        }

        $builder
            ->add('locale', EntityType::class, ['class' => Locale::class, 'choice_label' => 'code', 'label' => false, 'mapped' => false])
            ->add('optionValue', EntityType::class, ['class' => ProductOptionValue::class, 'choices' => $optionValueChoices, 'choice_label' => 'code', 'choice_attr' => function (ProductOptionValue $choice, $key) use ($options) {
                if (!$choice->getEnabled()) {
                    return ['disabled' => true];
                }

                return ['data-selected' => $choice->getOption()->getDefaultOptionValueByLocale($options['locale'])];
            }, 'label' => false, 'mapped' => false, ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['locale' => 'fr_FR']);
    }

    public function getBlockPrefix()
    {
        return 'sylius_product_option_locale_default_locale';
    }
}
