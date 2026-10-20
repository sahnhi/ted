<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSTechnicalInfosType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'translations',
                ResourceTranslationsType::class,
                [
                    'entry_type' => ProductCMSTechnicalInfosTranslationsType::class,
                ]
            )
            ->add(
                'productCMSTechnicalDetails',
                CollectionType::class,
                [
                    'entry_type' => ProductCMSTechnicalDetailsType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'label' => 'app.form.product.cms.technical_infos.details',
                    'prototype' => true,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_form_cms_technical_infos';
    }
}
