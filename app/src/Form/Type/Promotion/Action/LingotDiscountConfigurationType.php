<?php

namespace App\Form\Type\Promotion\Action;

use App\Form\Type\Promotion\Configuration\FiltersConfigurationType;
use Sylius\Bundle\CoreBundle\Form\Type\ChannelCollectionType;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LingotDiscountConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lingot', CheckboxType::class, [
                'required' => false,
                'label' => 'sylius.ui.lingot',
            ])
            ->add('maxGift', IntegerType::class, [
                'required' => false,
                'label' => 'sylius.ui.maxGift',
            ])
        ;
    }

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

    public function getBlockPrefix()
    {
        return 'sylius_promotion_action_lingot_discount_configuration';
    }
}
