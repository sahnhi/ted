<?php

namespace App\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminProductBundleFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', ChoiceType::class, [
                'label' => 'sylius.ui.isBundle',
                'choices' => [
                    '' => '',
                    'sylius.ui.yes' => 1,
                    'sylius.ui.no' => 2,
                ],
            ])
        ;
    }
}
