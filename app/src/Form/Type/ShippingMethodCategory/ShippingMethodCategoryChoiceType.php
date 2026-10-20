<?php

declare(strict_types=1);

namespace App\Form\Type\ShippingMethodCategory;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\ShippingMethodCategoryService;
use App\Doctrine\Entity\Sylius\Order;
use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodCategoryChoiceType extends AbstractType
{
    public function __construct(
        private readonly MoneyFormatterInterface $moneyFormatter
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var iterable<ShippingMethodCategory> $choices */
        $choices = $options['shipping_method_categories'];
        /** @var Order $order */
        $order = $options['order'];

        $builder
            ->add('code', ChoiceType::class, [
                'label' => 'app.ui.shipping_method_categories',
                'mapped' => false,
                'required' => true,
                'choices' => $choices,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'choice_name' => 'code',
                'multiple' => false,
                'expanded' => true,
            ]);

        foreach ($choices as $category) {
            $formName = sprintf('services_%s', $category->getCode());

            $builder
                ->add($formName, ChoiceType::class, [
                    'label' => 'app.ui.shipping_method_category.available_paid_services',
                    'mapped' => false,
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $options['choices_callback']($category),
                    'choice_value' => $options['service_value_callback'],
                    'choice_label' => function (ShippingMethodCategoryService $service) use ($order): string {
                        if ($service->isIncluded()) {
                            return sprintf('Inclus - %s', $service->getName());
                        }

                        $variant = $service->getProductVariant();

                        $channelPricing = $variant->getChannelPricingForChannel($order->getChannel());
                        if (null === $channelPricing) {
                            return $service->getName();
                        }

                        $formattedPrice = $this->moneyFormatter->format($channelPricing->getPrice(), $order->getCurrencyCode(), $order->getLocaleCode());

                        return sprintf('%s - %s', $service->getName(), $formattedPrice);
                    },
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $serviceValueCallback = static fn (ShippingMethodCategoryService $service): ?string => $service->getCode();
        $choicesCallback = static fn (ShippingMethodCategory $category): iterable => $category->getPaidServices();

        $resolver
            ->setRequired(['shipping_method_categories', 'choices_callback', 'service_value_callback', 'order'])
            ->setAllowedTypes('shipping_method_categories', 'iterable')
            ->setAllowedTypes('choices_callback', 'callable')
            ->setAllowedTypes('service_value_callback', 'callable')
            ->setAllowedTypes('order', Order::class)
            ->setDefault('service_value_callback', $serviceValueCallback)
            ->setDefault('choices_callback', $choicesCallback)
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['shipping_method_categories'] = $options['shipping_method_categories'];
    }
}
