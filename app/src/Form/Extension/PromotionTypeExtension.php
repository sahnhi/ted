<?php

namespace App\Form\Extension;

use Sylius\Bundle\PromotionBundle\Form\Type\PromotionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class PromotionTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    /**
     * {@inherit}.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('couponBased', CheckboxType::class, [
                'label' => 'sylius.form.promotion.coupon_based',
                'value' => true,
            ])
            ->add('exceptional', CheckboxType::class, [
                'label' => 'app.form.promotion.exceptional',
                'disabled' => true,
            ])
            ->add('automaticallyApplied', CheckboxType::class, [
                'label' => 'app.form.promotion.automatically_applied',
            ])
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $form = $event->getForm();
                $promotion = $event->getData();

                if (!$promotion) {
                    return;
                }

                if ($promotion->isExclusive() === $promotion->isExceptional()) {
                    $promotion->setExceptional(0);
                }

                $exclusiveDisabled = true;

                if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                    $exclusiveDisabled = false;
                }

                $form->add('exclusive', CheckboxType::class, [
                    'label' => 'sylius.form.promotion.exclusive',
                    'value' => true,
                    'disabled' => $exclusiveDisabled,
                ]);
            })
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            $form = $event->getForm();

            if (null === $data->getId()) {
                $options = $form->get('code')->getConfig()->getOptions();
                $options['disabled'] = false;

                $form->add('code', TextType::class, $options);
            }
        });
    }

    /**
     * {@inherit}.
     */
    public static function getExtendedTypes(): iterable
    {
        return [PromotionType::class];
    }
}
