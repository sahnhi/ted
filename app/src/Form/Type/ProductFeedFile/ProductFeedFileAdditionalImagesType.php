<?php

declare(strict_types=1);

namespace App\Form\Type\ProductFeedFile;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductFeedFileAdditionalImagesType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'url',
                UrlType::class,
                [
                    'label' => false,
                    'required' => true,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_feed_file_additional_images';
    }
}
