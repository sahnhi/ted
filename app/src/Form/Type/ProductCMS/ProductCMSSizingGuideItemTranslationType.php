<?php

declare(strict_types=1);

namespace App\Form\Type\ProductCMS;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSSizingGuideItemTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'app.form.product.cms.sizing_guide.item.name',
                ]
            )
            ->add(
                'imagePath',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'app.form.product.cms.sizing_guide.item.image_path',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => 'app.form.product.cms.sizing_guide.item.description',
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_cms_sizing_guide_item_translations';
    }
}
