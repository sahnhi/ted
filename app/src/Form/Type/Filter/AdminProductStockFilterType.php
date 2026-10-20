<?php

namespace App\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminProductStockFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('onHand', ChoiceType::class, [
                'label' => 'sylius.ui.onhand.label',
                'choices' => array_merge(
                    [
                        'sylius.ui.onhand.equal' => '=',
                        'sylius.ui.onhand.greater' => '>',
                        'sylius.ui.onhand.greater_or_equal' => '>=',
                        'sylius.ui.onhand.less' => '<',
                        'sylius.ui.onhand.less_or_equal' => '<=',
                    ],
                    $options['stock']
                ),
            ])
            ->add('stockValue', NumberType::class, [
                'label' => 'sylius.ui.value',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'stock' => [],
            ])
            ->setAllowedTypes('stock', ['array'])
        ;
    }
}
