<?php

declare(strict_types=1);

namespace App\Form\Type\Rule;

use App\Doctrine\Entity\ShippingMethodCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class ShipmentHasTheShippingMethodCategoryConfigurationType extends AbstractType
{
    public function __construct(
        private readonly DataTransformerInterface $shippingMethodCategoryToCodeTransformer
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shippingMethodCategory', EntityType::class, [
                'class' => ShippingMethodCategory::class,
                'multiple' => false,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'label' => 'app.ui.shipping_method_categories',
            ]);

        $builder->get('shippingMethodCategory')->addModelTransformer($this->shippingMethodCategoryToCodeTransformer);
    }
}
