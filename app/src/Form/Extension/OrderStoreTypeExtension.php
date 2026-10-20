<?php

namespace App\Form\Extension;

use App\Form\Type\OrderStore\OrderStoreType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class OrderStoreTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('orderStore', OrderStoreType::class);
    }

    public static function getExtendedTypes(): iterable
    {
        return [CompleteType::class];
    }
}
