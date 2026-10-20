<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSTagsTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'primaryTag',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.tags.primary_tag',
                    'required' => false,
                    'help' => 'app.form.product.cms.tags.primary_tag_help',
                ]
            )
            ->add(
                'secondaryTag',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.tags.secondary_tag',
                    'required' => false,
                    'help' => 'app.form.product.cms.tags.secondary_tag_help',
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_form_cms_tags_translation';
    }
}
