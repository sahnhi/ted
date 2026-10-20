<?php

namespace App\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductVariantChoiceTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_label' => fn ($variant) => $variant->getName(),
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductVariantChoiceType::class];
    }
}
