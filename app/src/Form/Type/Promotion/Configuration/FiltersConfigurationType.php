<?php

namespace App\Form\Type\Promotion\Configuration;

use Sylius\Bundle\PromotionBundle\Form\Type\PromotionFilterCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FiltersConfigurationType extends AbstractType
{
    /** {@inheritdoc} Custom function from original file */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filters', PromotionFilterCollectionType::class, [
                'label' => false,
                'required' => false,
                'currency' => $options['currency'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('currency')
            ->setAllowedTypes('currency', 'string')
        ;
    }

    /** {@inheritdoc} Custom function from original file */
    public function getBlockPrefix(): string
    {
        return 'sylius_promotion_action_filters_configuration';
    }
}
