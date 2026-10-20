<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Ecotax;
use App\Doctrine\Entity\ProductVariantEcotax;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductVariantEcotaxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ecotax', EntityType::class, [
                'label' => 'sylius.form.ecotax.label',
                'class' => Ecotax::class,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'sylius.form.ecotax.quantity',
            ])
            ->add('manufacturerPaid', ChoiceType::class, [
                'label' => 'sylius.form.ecotax.manufacturerPaid',
                'choices' => [
                    'sylius.form.ecotax.notpaid' => ProductVariantEcotax::MANUFACTURER_NOTPAID,
                    'sylius.form.ecotax.paid' => ProductVariantEcotax::MANUFACTURER_PAID,
                    'sylius.form.ecotax.return' => ProductVariantEcotax::MANUFACTURER_RETURN,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariantEcotax::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_product_variant_ecotax';
    }
}
