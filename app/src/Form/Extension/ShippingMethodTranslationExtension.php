<?php

namespace App\Form\Extension;

use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

final class ShippingMethodTranslationExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('deliveryConditions', TextareaType::class, [
            'label' => 'app.form.shipping_method.delivery_conditions.label',
            'required' => false,
        ]);
    }

    /**
     * @return string[]
     */
    public static function getExtendedTypes(): array
    {
        return [ShippingMethodTranslationType::class];
    }
}
