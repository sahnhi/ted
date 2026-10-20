<?php

declare(strict_types=1);

namespace App\Form\Type\ShippingMethod;

use App\Form\Type\ProductVariantAutocompleteChoiceType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAutocompleteChoiceType;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ShippingMethodCategoryServiceType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber(
                options: ['constraints' => [new NotBlank(['groups' => $this->validationGroups])]]
            ))
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'label' => 'sylius.ui.translations',
                'entry_type' => ShippingMethodCategoryServiceTranslationType::class,
            ])
            ->add('productVariant', ProductVariantAutocompleteChoiceType::class, [
                'label' => 'app.ui.shipping_method_category.linked_variant',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => $this->validationGroups]),
                ],
            ])
            ->add('included', CheckboxType::class, [
                'label' => 'app.ui.shipping_method_category.included_service',
            ])
            ->add('restrictedProducts', ProductAutocompleteChoiceType::class, [
                'label' => 'app.ui.shipping_method_category.restricted_products_label',
                'multiple' => true,
                'by_reference' => false,
            ])
        ;
    }
}
