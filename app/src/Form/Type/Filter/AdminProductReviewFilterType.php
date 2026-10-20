<?php

namespace App\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminProductReviewFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->getName(), ChoiceType::class, [
                'label' => 'app.form.product_review.' . $builder->getName(),
                'choices' => array_merge(
                    ['Tous' => ''],
                    $options['state']
                ),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'state' => [],
            ])
            ->setAllowedTypes('state', ['array'])
        ;
    }
}
