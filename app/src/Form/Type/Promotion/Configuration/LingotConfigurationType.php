<?php

namespace App\Form\Type\Promotion\Configuration;

use App\Services\Drip\DripUrlBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\Translation\TranslatorInterface;

class LingotConfigurationType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly DripUrlBuilder $dripUrlBuilder
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var array $data */
            $data = $event->getData();
            $form = $event->getForm();

            $note = null;

            if (null !== $data && null !== ($code = $data['dripCode'] ?? null)) {
                $note = $this->translator->trans('app.ui.drip.url') . ' ' . $this->dripUrlBuilder->buildUrl($code);
            }

            $form->add('dripCode', TextType::class, [
                'label' => 'app.ui.drip.liquid_shortcode',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
                'help' => $note,
            ]);
        });

        $builder
            ->add('message', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'app.ui.coupon_duration',
                'required' => true,
                'empty_data' => 2,
                'constraints' => [
                    new Type(['type' => 'integer', 'groups' => 'sylius']),
                    new NotBlank(['groups' => 'sylius']),
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_promotion_rule_lingot_configuration';
    }
}
