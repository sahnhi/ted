<?php

namespace App\Form\Type\Loyalty;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class LoyaltyPromotionCouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'label' => 'app.ui.loyalty.email.label',
                'mapped' => false,
            ])
            ->add('coupon', TextType::class, [
                'label' => 'app.ui.loyalty.coupon.label',
                'mapped' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_loyalty_promotion_coupon';
    }
}
