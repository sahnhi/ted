<?php

namespace App\Form\Extension;

use App\Form\Type\Loyalty\LoyaltyPromotionCouponType;
use Sylius\Bundle\OrderBundle\Form\Type\CartType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class CartTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('loyaltyCoupon', LoyaltyPromotionCouponType::class, [
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [CartType::class];
    }
}
