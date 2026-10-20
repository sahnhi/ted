<?php

/*
 * This file was created by developers working at Sokeo
 * Do you need more information about us and what we do? Visit our https://sokeo.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://sokeo.io/career
*/

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

final class YounitedpayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('apiKeyPublicProd', TextType::class, [
                'label' => 'sokeo_sylius_younitedpay.ui.api_key_public_prod',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sokeo_sylius_younitedpay.api_key_public_prod.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('apiKeyPrivateProd', TextType::class, [
                'label' => 'sokeo_sylius_younitedpay.ui.api_key_private_prod',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sokeo_sylius_younitedpay.api_key_private_prod.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('apiKeyPublicSandbox', TextType::class, [
                'label' => 'sokeo_sylius_younitedpay.ui.api_key_public_sandbox',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sokeo_sylius_younitedpay.api_key_public_sandbox.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('apiKeyPrivateSandbox', TextType::class, [
                'label' => 'sokeo_sylius_younitedpay.ui.api_key_private_sandbox',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sokeo_sylius_younitedpay.api_key_private_sandbox.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('amountMin', TextType::class, [
                'label' => 'sokeo_sylius_younitedpay.ui.amount_min',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sokeo_sylius_younitedpay.amount_min.not_blank',
                        'groups' => 'sylius',
                    ]),
                    new PositiveOrZero([
                      'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('amountMax', TextType::class, [
                'label' => 'sokeo_sylius_younitedpay.ui.amount_max',
                'attr' => [
                  'placeholder' => 0,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'sokeo_sylius_younitedpay.amount_max.not_blank',
                        'groups' => 'sylius',
                    ]),
                    new PositiveOrZero([
                      'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('monthlyPayments', TextType::class, [
                'label' => 'sokeo_sylius_younitedpay.ui.monthly_payments',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sokeo_sylius_younitedpay.monthly_payments.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('sandbox', CheckboxType::class, [
                'label' => 'sokeo_sylius_younitedpay.ui.sandbox',
            ])
        ;
    }
}
