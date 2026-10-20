<?php

namespace App\Form\Type\Bundle;

use App\Doctrine\Entity\BundleGeneratorProduct;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class BundleProductConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('product', ProductAutocompleteChoiceType::class, [
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
        ])
            ->add('quantity', IntegerType::class, [
                'required' => true,
                'label' => false,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 1,
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('isFree', CheckboxType::class, [
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BundleGeneratorProduct::class,
            'validation_groups' => ['sylius'],
        ]);
    }
}
