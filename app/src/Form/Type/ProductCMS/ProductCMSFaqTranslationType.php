<?php

declare(strict_types=1);

namespace App\Form\Type\ProductCMS;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductCMSFaqTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'required' => true,
            'label' => 'app.form.product.cms.cms_faq.header',
        ]);
    }
}
