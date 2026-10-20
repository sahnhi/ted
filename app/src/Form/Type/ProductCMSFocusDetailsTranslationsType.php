<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSFocusDetailsTranslationsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'term',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.cms_focus.term',
                    'required' => false,
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'app.form.product.cms.cms_focus.description',
                    'required' => false,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_cms_focus_details_translations';
    }
}
