<?php

declare(strict_types=1);

namespace App\Form\Type\Rule;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderHasCouponConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('coupon', TextType::class, [
                'label' => 'sylius.ui.coupon',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }
}
