<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'subTitle',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.sub_title',
                    'help' => 'app.form.product.cms.sub_title_help',
                ]
            )
            ->add(
                'subInfo',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.sub_info',
                ]
            )
            ->add(
                'subInfoBis',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.sub_info_bis',
                ]
            )
            ->add(
                'productDetailsTitle',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.product_details_title',
                    'required' => false,
                ]
            )
            ->add(
                'productDetailsSubTitle',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.product_details_sub_title',
                    'required' => false,
                ]
            )
            ->add(
                'productDetailsSubClaim',
                TextareaType::class,
                [
                    'label' => 'app.form.product.cms.product_details_sub_claim',
                    'help' => 'app.form.product.cms.product_details_sub_claim_help',
                    'required' => false,
                ]
            )
            ->add(
                'productDetailsSubTitleBis',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.product_details_sub_title_bis',
                    'required' => false,
                ]
            )
            ->add(
                'productDetailsSubClaimBis',
                TextareaType::class,
                [
                    'label' => 'app.form.product.cms.product_details_sub_claim_bis',
                    'help' => 'app.form.product.cms.product_details_sub_claim_bis_help',
                    'required' => false,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_cms_translation';
    }
}
