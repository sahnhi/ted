<?php

declare(strict_types=1);

namespace App\Form\Type\ProductCMS;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSSizingGuideTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'app.form.product.cms.sizing_guide.title',
            ])
            ->add('subTitle', TextType::class, [
                'label' => 'app.form.product.cms.sizing_guide.sub_title',
                'required' => false,
            ])
            ->add('link', TextType::class, [
                'label' => 'app.form.product.cms.sizing_guide.link',
                'required' => false,
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_cms_sizing_guide_translation';
    }
}
