<?php

namespace App\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Product\ChannelPricingType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ChannelPricingTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): array
    {
        return [ChannelPricingType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->get('price')
            ->setDisabled(true);
    }
}
