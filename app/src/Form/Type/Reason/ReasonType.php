<?php

namespace App\Form\Type\Reason;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\ReasonGroup;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAutocompleteChoiceType;
use Sylius\Component\Core\Model\Channel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enable', CheckboxType::class, [
                'label' => 'sylius.ui.enable',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'sylius.form.reason.type.product_support' => Reason::TYPE_PRODUCT_SUPPORT,
                    'sylius.form.reason.type.commercial_offer' => Reason::TYPE_COMMERCIAL_OFFER,
                ],
                'label' => 'sylius.ui.reason_type',
            ])
            ->add('groupName', EntityType::class, [
                'label' => 'sylius.ui.reason_group_name',
                'class' => ReasonGroup::class,
                'choice_label' => 'name',
            ])
            ->add('name', TextType::class, ['label' => 'sylius.ui.name'])
            ->add('products', ProductAutocompleteChoiceType::class, [
                'multiple' => true,
            ])
            ->add('channel', EntityType::class, [
                'class' => Channel::class,
                'choice_label' => 'name',
                'label' => 'sylius.ui.channel',
                'placeholder' => 'sylius.form.reason.channel_placeholder',
                'required' => false,
            ])
            ->add('placeholder', TextareaType::class, [
                'label' => 'sylius.form.order_reason.placeholder',
                'required' => false,
            ])
            ->add('productRule', ChoiceType::class, [
                'choices' => [
                    'sylius.ui.product_rule.1' => Reason::PRODUCT_RULE_NO_SELECTION,
                    'sylius.ui.product_rule.2' => Reason::PRODUCT_RULE_IDENTICAL,
                    'sylius.ui.product_rule.3' => Reason::PRODUCT_RULE_SIMPLE,
                    'sylius.ui.product_rule.4' => Reason::PRODUCT_RULE_QUALITY,
                ],
                'label' => 'sylius.form.reason.product_rule',
            ])
            ->add('returnType', ChoiceType::class, [
                'choices' => [
                    'sylius.form.order_reason.return_type.exchange' => Reason::RETURN_TYPE_EXCHANGE,
                    'sylius.form.order_reason.return_type.normal' => Reason::RETURN_TYPE_NORMAL,
                    'sylius.form.order_reason.return_type.return_quality' => Reason::RETURN_TYPE_QUALITY,
                    'sylius.form.order_reason.return_type.exchange_quality' => Reason::RETURN_TYPE_QUALITY_EXCHANGE,
                    'sylius.form.order_reason.return_type.refund' => Reason::RETURN_TYPE_REFUND,
                    'sylius.form.order_reason.return_type.sender' => Reason::RETURN_TYPE_SENDER,
                    'sylius.form.order_reason.return_type.autonomous' => Reason::RETURN_TYPE_AUTONOMOUS,
                ],
                'label' => 'sylius.form.order_reason.return_type.label',
                'placeholder' => 'sylius.form.order_reason.return_type.placeholder',
                'required' => false,
            ])
            ->add('templateOptions', ChoiceType::class, [
                'choices' => Reason::TEMPLATE_OPTIONS,
                'label' => 'sylius.ui.reason.template_options_label',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Reason::class]);
    }
}
