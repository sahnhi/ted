<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\ShippingStock;
use App\Doctrine\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'label' => 'sylius.ui.shipping_stock.warehouse',
            ])
            ->add('shippingDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'sylius.ui.shipping_stock.date',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShippingStock::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_product_variant_shipping_stock';
    }
}
