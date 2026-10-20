<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\ProductCombo;
use App\Doctrine\Entity\Sylius\ProductVariant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductComboType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productVariant', EntityType::class, [
                'class' => ProductVariant::class,
                'label' => 'sylius.form.product_combo.product_variant',
                'choice_label' => 'nameAndCode',
                'attr' => [
                    'class' => 'select2',
                ],
            ])
            ->add('productVariantLogistic', EntityType::class, [
                'class' => ProductVariant::class,
                'label' => 'sylius.form.product_combo.product_variant_logistic',
                'choice_label' => 'nameAndCode',
                'attr' => [
                    'class' => 'select2',
                ],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'sylius.ui.quantity',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductCombo::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_product_combo';
    }
}
