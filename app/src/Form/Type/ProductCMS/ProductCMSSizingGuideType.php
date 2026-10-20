<?php

declare(strict_types=1);

namespace App\Form\Type\ProductCMS;

use App\Doctrine\Entity\ProductCMSSizingGuideItem;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAutocompleteChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductCMSSizingGuideType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class)
            ->add('enabled', CheckboxType::class)
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ProductCMSSizingGuideTranslationType::class,
            ])
            ->add('products', ProductAutocompleteChoiceType::class, [
                'multiple' => true,
                'label' => 'app.ui.bundle_association.steps.products.title',
            ])
            ->add('productCMSSizingGuideItems', CollectionType::class, [
                'entry_type' => ProductCMSSizingGuideItemType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $countData = count($event->getData()->getProductCMSSizingGuideItems());
                $data = $event->getData();
                for ($i = ++$countData; $i <= 3; ++$i) {
                    $productCMSSizingGuideItem = new ProductCMSSizingGuideItem();
                    $data->addProductCMSSizingGuideItem($productCMSSizingGuideItem);
                }
            })
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_cms_sizing_guide';
    }
}
