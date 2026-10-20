<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Checker\Shipping\Rule\GreaterThanTheMaximumAllowedMaxArticlesRuleChecker;
use App\Checker\Shipping\Rule\LowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodRuleType;
use Sylius\Component\Shipping\Model\ShippingMethodRule;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ShippingMethodRuleTypeExtension extends AbstractTypeExtension
{
    public const SHIPPING_METHOD_CODE_INDEX = 'shippingMethodCode';

    private const SHIPPING_METHOD_AWARE_RULES = [
        LowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker::TYPE,
        GreaterThanTheMaximumAllowedMaxArticlesRuleChecker::TYPE,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $data = $event->getData();
                if (!$data instanceof ShippingMethodRule || !in_array($data->getType(), self::SHIPPING_METHOD_AWARE_RULES, true)) {
                    return;
                }

                $configuration = $data->getConfiguration();
                $configuration[self::SHIPPING_METHOD_CODE_INDEX] = $data->getShippingMethod()?->getCode();

                $data->setConfiguration($configuration);
            });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodRuleType::class];
    }
}
