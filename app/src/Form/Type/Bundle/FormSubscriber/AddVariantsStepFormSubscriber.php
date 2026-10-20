<?php

namespace App\Form\Type\Bundle\FormSubscriber;

use App\Doctrine\Entity\BundleGeneratorInterface;
use App\Doctrine\Entity\BundleGeneratorOptionInterface;
use App\Doctrine\Entity\BundleGeneratorProductInterface;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Form\Type\Bundle\BundleCombinationConfigurationType;
use App\Form\Type\Bundle\BundleVariantConfigurationType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class AddVariantsStepFormSubscriber implements EventSubscriberInterface
{
    public const BUNDLE_GENERATION_STEP = 'variants';

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
        ];
    }

    public function postSetData(FormEvent $event): void
    {
        /** @var BundleGeneratorInterface $data */
        $data = $event->getData();
        $form = $event->getForm();

        $form->add('combinations', CollectionType::class, [
            'entry_type' => BundleCombinationConfigurationType::class,
            'label' => false,
            'entry_options' => [
                'label' => false,
            ],
        ]);

        $form->add('apply_selector_product', EntityType::class, [
            'class' => Product::class,
            'mapped' => false,
            'label' => false,
            'required' => false,
            'placeholder' => 'app.ui.bundle_association.labels.select_product',
            'choices' => $this->makeChoiceListForProduct($data->getProducts()->toArray()),
            'attr' => ['class' => 'select2'],
        ]);

        /** @var BundleGeneratorProductInterface $productConfigurator */
        foreach ($data->getProducts() as $productConfigurator) {
            $form->add('apply_selector_variant_' . $productConfigurator->getProduct()->getCode(), EntityType::class, [
                'class' => ProductVariant::class,
                'mapped' => false,
                'label' => false,
                'required' => false,
                'placeholder' => 'app.ui.bundle_association.labels.select_variant',
                'choices' => $this->makeChoiceListForVariant($productConfigurator->getProduct()),
                'attr' => ['class' => 'select2', 'data-product' => $productConfigurator->getProduct()->getId()],
                'choice_label' => 'code',
            ]);
        }

        /** @var BundleGeneratorOptionInterface $optionGenerator */
        foreach ($data->getOptions() as $optionGenerator) {
            $form->add('filter_' . $optionGenerator->getProductOption()->getCode(), ChoiceType::class, [
                'mapped' => false,
                'choices' => $this->makeChoiceListForOption($optionGenerator->getProductOption()),
                'expanded' => true,
                'multiple' => true,
                'label' => false,
                'required' => false,
                'data' => $optionGenerator->isForcedValue() ? [$optionGenerator->getProductOptionValue()->getId()] : null,
                'disabled' => $optionGenerator->isForcedValue(),
            ]);
        }

        /** @var FormInterface $child */
        foreach ($form->get('combinations') as $child) {
            $childData = $child->getData();
            if ($childData->isWillBeGenerated()) {
                $child->add('variants', CollectionType::class, [
                    'entry_type' => BundleVariantConfigurationType::class,
                    'label' => false,
                    'prototype' => false,
                ]);
            }
        }
    }

    private function makeChoiceListForOption(ProductOptionInterface $option): array
    {
        $values = [];

        foreach ($option->getValues() as $value) {
            $values[$value->getValue()] = $value->getId();
        }

        return $values;
    }

    private function makeChoiceListForProduct(array $products): array
    {
        $values = [];

        /** @var BundleGeneratorProductInterface $productConfigurator */
        foreach ($products as $productConfigurator) {
            $values[] = $productConfigurator->getProduct();
        }

        return $values;
    }

    private function makeChoiceListForVariant(ProductInterface $product): array
    {
        $values = [];

        foreach ($product->getVariants() as $variant) {
            $values[] = $variant;
        }

        return $values;
    }
}
