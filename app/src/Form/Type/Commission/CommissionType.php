<?php

namespace App\Form\Type\Commission;

use App\Doctrine\Entity\Commission;
use App\Doctrine\Entity\Sylius\Product;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commission', MoneyType::class, [
                'label' => 'sylius.ui.commission',
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'sylius.form.product.label',
                'placeholder' => 'sylius.form.product.placeholder',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Commission::class]);
    }
}
