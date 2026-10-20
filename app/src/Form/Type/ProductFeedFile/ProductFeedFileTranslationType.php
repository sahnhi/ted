<?php

declare(strict_types=1);

namespace App\Form\Type\ProductFeedFile;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductFeedFileTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.title',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'app.form.product_feed_file.description',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'googleCategory',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.google_category',
                    'required' => true,
                    'empty_data' => '',
                    'help' => '<a href="https://support.google.com/merchants/answer/7052112?sjid=11853545144534087325-EU#product_category" target="_blank">help</a>',
                    'help_html' => true,
                ]
            )
            ->add(
                'productType',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.product_type',
                    'required' => true,
                    'empty_data' => '',
                    'help' => '<a href="https://support.google.com/merchants/answer/7052112?sjid=11853545144534087325-EU#product_category" target="_blank">help</a>',
                    'help_html' => true,
                ]
            )
            ->add(
                'material',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.material',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'url',
                UrlType::class,
                [
                    'label' => 'app.form.product_feed_file.url',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'objectState',
                ChoiceType::class,
                [
                    'label' => 'app.form.product_feed_file.object_state',
                    'choices' => [
                        'new' => 'new',
                        'used' => 'used',
                        'refurbished' => 'refurbished',
                    ],
                ]
            )
            ->add(
                'promotionIdentifier',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.promotion_identifier',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'groupIdentifier',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.group_identifier',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'sizingSystem',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.sizing_system',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'composition',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.composition',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'tags',
                TextType::class,
                [
                    'label' => 'app.form.product_feed_file.tags',
                    'required' => true,
                    'empty_data' => '',
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_feed_file_translation';
    }
}
