<?php

declare(strict_types=1);

namespace App\Form\Type\ProductCMS;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductCMSSizingGuideItemType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ProductCMSSizingGuideItemTranslationType::class,
                'label' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_cms_sizing_guide_item';
    }
}
