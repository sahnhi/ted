<?php

declare(strict_types=1);

namespace App\Form\Type\ProductCMS;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface as FormFormBuilderInterface;

final class ProductCMSClaimsTranslationType extends AbstractResourceType
{
    public function buildForm(FormFormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'claim',
            TextType::class,
            [
                'required' => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_cms_claims_translation';
    }
}
