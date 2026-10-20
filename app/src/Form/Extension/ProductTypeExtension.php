<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\ProductCMSClaims;
use App\Doctrine\Entity\ProductCMSFocus;
use App\Doctrine\Entity\ProductCMSFocusDetails;
use App\Doctrine\Entity\ProductCMSTechnicalDetails;
use App\Doctrine\Entity\ProductCMSTechnicalInfos;
use App\Doctrine\Entity\RtbInfos;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Enum\ProductBundleType;
use App\Form\Type\MetaDataType;
use App\Form\Type\ProductCMS\ProductCMSClaimsType;
use App\Form\Type\ProductCMS\ProductCMSFaqType;
use App\Form\Type\ProductCMSFocusType;
use App\Form\Type\ProductCMSTagsType;
use App\Form\Type\ProductCMSTechnicalInfosType;
use App\Form\Type\ProductCMSType;
use App\Form\Type\ProductGoogleRssType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weighting', NumberType::class, [
                'label' => 'sylius.ui.product.weighting',
            ])
            ->add('reviewRequested', CheckboxType::class, [
                'label' => 'sylius.ui.product.review_requested',
                'required' => false,
            ])
            ->add('productGoogleRss', ProductGoogleRssType::class, [
                'required' => false,
                'label' => 'sylius.ui.products.google_rss',
            ])
            ->add('warranty', NumberType::class, [
                'required' => false,
                'label' => 'sylius.ui.product.warranty',
            ])
            ->add('metaData', CollectionType::class, [
                'entry_type' => MetaDataType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => 'sylius.ui.product.meta_data',
            ])
            ->add('joinStoryLink', TextareaType::class, [
                'required' => false,
                'label' => 'sylius.ui.product.join_story_link',
            ])
            ->add('bundleType', ChoiceType::class, [
                'required' => false,
                'choices' => ProductBundleType::cases(),
                'choice_label' => 'name',
                'choice_value' => 'value',
                'label' => 'sylius.ui.product.bundle_type',
            ])
            ->add('productCMSTags', ProductCMSTagsType::class, [
                'required' => false,
            ])
            ->add('productCMS', ProductCMSType::class, [
                'label' => false,
            ])
            ->add('claims', CollectionType::class, [
                'entry_type' => ProductCMSClaimsType::class,
                'by_reference' => false,
                'prototype' => true,
                'delete_empty' => function (ProductCMSClaims $product): void {
                    if ('a' === $product->getClaim('fr_FR')) {
                        unset($product);
                    }
                },
            ])
            ->add('productCMSTechnicalInfos', CollectionType::class, [
                'entry_type' => ProductCMSTechnicalInfosType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'app.form.product_cms_technical_infos.infos',
                'prototype' => true,
            ])
            ->add('productCMSFocus', ProductCMSFocusType::class, [
                'label' => 'sylius.ui.product.gift_card',
                'required' => false,
            ])
            ->add('productCMSFaq', ProductCMSFaqType::class, [
                'label' => false,
                'required' => true,
            ])
            ->add('template', ChoiceType::class, [
                'label' => 'app.form.product.template',
                'required' => true,
                'choices' => [
                    'Classique' => 'classique',
                    'Grand Ours' => 'grand_ours',
                ],
            ])
            ->add('rtbInfos', EntityType::class, [
                'class' => RtbInfos::class,
                'choice_label' => 'label',
                'multiple' => true,
            ])
            ->add('canGenerateBundle', CheckboxType::class, [
                'label' => 'app.form.product.can_generate_bundle',
                'required' => true,
            ])
            ->add('quantitySelector', CheckboxType::class, [
                'label' => 'app.form.product.quantity_selector',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $product = $event->getData();
                $total = count($product->getClaims()->toArray());
                for ($i = $total; $i < 5; ++$i) {
                    $tempClaim = new ProductCMSClaims();
                    $tempClaim->setClaim(ProductCMSClaims::PLACEHOLDER, 'fr_FR');
                    $product->addClaim($tempClaim);
                }

                $this->populateTechnicalInfos($product);
                $this->popoulateFocus($product);
            });

        if ($builder->get('productGoogleRss')->has('productUrl')) {
            $builder->get('productGoogleRss')->remove('productUrl');
        }
    }

    private function populateTechnicalInfos(Product $product): void
    {
        $totalInfos = $product->getProductCMSTechnicalInfos()->count();
        for ($i = $totalInfos; $i < 2; ++$i) {
            $tempInfo = new ProductCMSTechnicalInfos();
            for ($j = 0; $j < 3; ++$j) {
                $tempDetail = new ProductCMSTechnicalDetails();
                $tempInfo->addProductCMSTechnicalDetail($tempDetail);
            }
            $product->addProductCMSTechnicalInfo($tempInfo);
        }
    }

    private function popoulateFocus(Product $product): void
    {
        if (null === $product->getProductCMSFocus()) {
            $product->setProductCMSFocus(new ProductCMSFocus());
        }

        /** @var ProductCMSFocus $focusDetails */
        $focusDetails = $product->getProductCMSFocus();

        $total = $focusDetails->getProductCMSFocusDetails()->count();
        for ($i = $total; $i < 5; ++$i) {
            $focusDetails->addProductCMSFocusDetail(new ProductCMSFocusDetails());
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
