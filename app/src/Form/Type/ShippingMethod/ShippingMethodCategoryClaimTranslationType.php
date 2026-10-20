<?php

declare(strict_types=1);

namespace App\Form\Type\ShippingMethod;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ShippingMethodCategoryClaimTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'sylius.ui.content',
                'required' => true,
            ])
        ;
    }
}
