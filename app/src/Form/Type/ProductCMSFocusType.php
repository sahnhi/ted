<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSFocusType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'productCMSFocusDetails',
                CollectionType::class,
                [
                    'entry_type' => ProductCMSFocusDetailsType::class,
                    'label' => 'app.form.product_cms_focus.product_cms_focus_details',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                ]
            )
            ->add(
                'translations',
                ResourceTranslationsType::class,
                [
                    'entry_type' => ProductCMSFocusTranslationsType::class,
                    'label' => 'app.form.product_cms_focus.translations',
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_cms_focus';
    }
}
