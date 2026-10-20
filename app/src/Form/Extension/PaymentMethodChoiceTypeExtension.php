<?php

namespace App\Form\Extension;

use App\Checker\PaymentMethodEligibleChecker;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodChoiceType;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentMethodChoiceTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly PaymentMethodsResolverInterface $paymentMethodsResolver,
        private readonly RepositoryInterface $paymentMethodRepository,
        private readonly PaymentMethodEligibleChecker $paymentMethodEligibleChecker
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choices' => function (Options $options) {
                    if (isset($options['subject'])) {
                        $availablePaymentMethods = $this->paymentMethodsResolver->getSupportedMethods($options['subject']);
                        $validPaymentMethods = [];
                        /** @var PaymentMethod $availablePaymentMethod */
                        foreach ($availablePaymentMethods as $availablePaymentMethod) {
                            if ($this->paymentMethodEligibleChecker->isPaymentEligible($options['subject']->getAmount(), $availablePaymentMethod)) {
                                $validPaymentMethods[] = $availablePaymentMethod;
                            }
                        }

                        return $validPaymentMethods;
                    }

                    return $this->paymentMethodRepository->findAll();
                },
                'choice_value' => 'code',
                'choice_label' => 'name',
                'choice_translation_domain' => false,
            ])
            ->setDefined([
                'subject',
            ])
            ->setAllowedTypes('subject', PaymentInterface::class);
    }

    public static function getExtendedTypes(): iterable
    {
        return [PaymentMethodChoiceType::class];
    }
}
