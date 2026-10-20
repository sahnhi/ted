<?php

declare(strict_types=1);

namespace App\Form\Type\ProductFeedFile;

use App\Form\Type\ProductVariantAutocompleteChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductFeedFileType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'productVariant',
                ProductVariantAutocompleteChoiceType::class,
                [
                    'label' => 'app.form.product_feed_file.product_variant',
                    'required' => true,
                ]
            )
            ->add(
                'googleId',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.google_id',
                    'required' => true,
                    'attr' => [
                        'readonly' => true,
                    ],
                ]
            )
            ->add(
                'mainImage',
                UrlType::class,
                [
                    'label' => 'app.form.product_feed_file.main_image',
                    'required' => false,
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'app.form.product_feed_file.enabled',
                    'required' => false,
                ]
            )
            ->add(
                'translations',
                ResourceTranslationsType::class,
                [
                    'entry_type' => ProductFeedFileTranslationType::class,
                ]
            )
            ->add(
                'additionalImages',
                CollectionType::class,
                [
                    'entry_type' => ProductFeedFileAdditionalImagesType::class,
                    'label' => 'app.form.product_feed_file.additional_images',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => true,
                    'required' => true,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_feed_file';
    }
}
