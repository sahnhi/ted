<?php

namespace App\Form\Type\Payment;

use App\Bridge\AdyenBridgeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdyenGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('environment', ChoiceType::class, [
                'choices' => [
                    'app.ui.gateway.adyen.form.environment.choices.production' => AdyenBridgeInterface::ENVIRONMENT_PRODUCTION,
                    'app.ui.gateway.adyen.form.environment.choices.development' => AdyenBridgeInterface::ENVIRONMENT_DEVELOPMENT,
                ],
                'label' => 'app.ui.gateway.adyen.form.environment.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.adyen.environment.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('endpointUrlPrefix', TextType::class, [
                'label' => 'app.ui.gateway.adyen.form.endpointUrlPrefix.label',
                'required' => false,
            ])
            ->add('merchantAccount', TextType::class, [
                'label' => 'app.ui.gateway.adyen.form.merchantAccount.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.adyen.merchantAccount.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('wsUser', TextType::class, [
                'label' => 'app.ui.gateway.adyen.form.wsUser.label',
                'required' => false,
            ])
            ->add('wsPassword', TextType::class, [
                'label' => 'app.ui.gateway.adyen.form.wsPassword.label',
                'required' => false,
            ])
            ->add('apiKey', TextType::class, [
                'label' => 'app.ui.gateway.adyen.form.apiKey.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.adyen.apiKey.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('clientKey', TextType::class, [
                'label' => 'app.ui.gateway.adyen.form.clientKey.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.adyen.clientKey.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('methodPayment', ChoiceType::class, [
                'label' => 'app.ui.gateway.adyen.form.methodPayment.label',
                'choices' => [
                    'app.ui.gateway.adyen.form.methodPayment.cb.label' => AdyenBridgeInterface::PAYMENT_METHOD_TYPE_CB,
                    'app.ui.gateway.adyen.form.methodPayment.paypal.label' => AdyenBridgeInterface::PAYMENT_METHOD_TYPE_PAYPAL,
                    'app.ui.gateway.adyen.form.methodPayment.paybylink.label' => AdyenBridgeInterface::PAYMENT_METHOD_TYPE_PAYBYLINK,
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.adyen.methodPayment.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('paypalMerchantId', TextType::class, [
                'label' => 'app.ui.gateway.adyen.form.methodPayment.paypal.merchandId.label',
                'required' => false,
            ])
            ->add('hmacNotification', TextType::class, [
                'label' => 'app.ui.gateway.adyen.form.hmacNotification.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.adyen.hmacNotification.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
        ;
    }
}
