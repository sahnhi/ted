<?php

namespace App\Form\Type\Promotion\Action;

use App\Form\Type\Promotion\Configuration\FiltersConfigurationType;
use Sylius\Bundle\CoreBundle\Form\Type\ChannelCollectionType;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GiftProductDiscountConfigurationType extends AbstractType
{
    /** {@inheritdoc} Custom function from original file */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('maxGifted', IntegerType::class)
            ->add('cartMinTotal', IntegerType::class, [
                'label' => 'sylius.form.promotion.cart_min_total',
                'required' => false,
            ])
        ;
    }

    /** {@inheritdoc} Custom function from original file */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => FiltersConfigurationType::class,
            'entry_options' => fn (ChannelInterface $channel): array => [
                'label' => $channel->getName(),
                'currency' => $channel->getBaseCurrency()->getCode(),
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChannelCollectionType::class;
    }

    /** {@inheritdoc} Custom function from original file */
    public function getBlockPrefix()
    {
        return 'sylius_promotion_action_gift_product_discount_configuration';
    }
}
