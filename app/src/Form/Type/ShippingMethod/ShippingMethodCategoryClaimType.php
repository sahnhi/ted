<?php

declare(strict_types=1);

namespace App\Form\Type\ShippingMethod;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class ShippingMethodCategoryClaimType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
            ])
            ->add('pushed', CheckboxType::class, [
                'label' => 'app.ui.shipping_method_category.claims.pushed',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'label' => 'sylius.ui.translations',
                'entry_type' => ShippingMethodCategoryClaimTranslationType::class,
            ])
        ;
    }
}
