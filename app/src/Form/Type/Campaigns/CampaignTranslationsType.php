<?php

declare(strict_types=1);

namespace App\Form\Type\Campaigns;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class CampaignTranslationsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'app.form.campaign.title',
                    'required' => true,
                    'help' => 'app.form.campaign.title_help',
                ]
            )
            ->add(
                'description',
                TextType::class,
                [
                    'label' => 'app.form.campaign.description',
                    'help' => 'app.form.campaign.description_help',
                    'required' => false,
                ]
            )
            ->add(
                'mainImageUrl',
                UrlType::class,
                [
                    'label' => 'app.form.campaign.main_image_url',
                    'help' => 'app.form.campaign.main_image_url_help',
                    'required' => false,
                ]
            )
            ->add(
                'mobileImageUrl',
                UrlType::class,
                [
                    'label' => 'app.form.campaign.mobile_image_url',
                    'help' => 'app.form.campaign.mobile_image_url_help',
                    'required' => false,
                ]
            )
            ->add(
                'url',
                TextType::class,
                [
                    'label' => 'app.form.campaign.url',
                    'required' => false,
                    'help' => 'app.form.campaign.url_help',
                ]
            )
            ->add(
                'metaTitle',
                TextType::class,
                [
                    'label' => 'app.form.campaign.meta_title',
                    'required' => false,
                    'help' => 'app.form.campaign.meta_title_help',
                ]
            )
            ->add(
                'metaDescription',
                TextType::class,
                [
                    'label' => 'app.form.campaign.meta_description',
                    'required' => false,
                ]
            )
            ->add(
                'metaKeywords',
                TextType::class,
                [
                    'label' => 'app.form.campaign.meta_keywords',
                    'required' => false,
                ]
            )
            ->add('seoBlockTitle', TextType::class, [
                'label' => 'app.form.campaign.seo_block_title',
                'required' => false,
                'help' => 'app.form.campaign.seo_block_title_help',
            ])
            ->add('seoBlockDescription', TextareaType::class, [
                'label' => 'app.form.campaign.seo_block_description',
                'required' => false,
                'help' => 'app.form.campaign.seo_block_description_help',
            ])
            ->add(
                'indexable',
                CheckboxType::class,
                [
                    'label' => 'app.form.campaign.indexable',
                    'required' => false,
                    'help' => 'app.form.campaign.indexable_help',
                ]
            )
            ->add(
                'followable',
                CheckboxType::class,
                [
                    'label' => 'app.form.campaign.followable',
                    'required' => false,
                    'help' => 'app.form.campaign.followable_help',
                ]
            )
            ->add(
                'startDate',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'label' => 'app.form.campaign.start_date',
                    'required' => true,
                ]
            )
            ->add(
                'endDate',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'label' => 'app.form.campaign.end_date',
                    'required' => true,
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'app.form.campaign.enabled_for_locale',
                    'required' => false,
                ]
            )
            ->add(
                'redirectUrl',
                UrlType::class,
                [
                    'label' => 'app.form.campaign.redirect_url',
                    'required' => false,
                    'help' => 'app.form.campaign.redirect_url_help',
                    'attr' => [
                        'checked' => 'checked',
                    ],
                ]
            )
            ->add(
                'redirectionHttpStatusCode',
                TextType::class,
                [
                    'label' => 'app.form.campaign.redirection_http_status_code',
                    'required' => false,
                    'help' => 'app.form.campaign.redirection_http_status_code_help',
                    'attr' => [
                        'checked' => 'checked',
                    ],
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_campaign_translations';
    }
}
