<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSTechnicalInfosTranslationsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'app.form.product.cms.technical_infos.subtitle',
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_form_cms_technical_infos_translations';
    }
}
