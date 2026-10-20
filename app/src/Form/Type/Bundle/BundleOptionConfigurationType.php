<?php

namespace App\Form\Type\Bundle;

use App\Doctrine\Entity\BundleGeneratorOption;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BundleOptionConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('forcedValue', CheckboxType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('position', HiddenType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'option-position',
                ],
            ])
            ->add('needsUnderscore', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'check-underscore',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            /** @var BundleGeneratorOption $data */
            $data = $event->getData();
            $form = $event->getForm();

            $form->add('productOptionValue', EntityType::class, [
                'required' => false,
                'class' => ProductOptionValue::class,
                'choice_name' => 'value',
                'choice_value' => 'id',
                'label' => false,
                'choices' => $data->getProductOption()->getValues(),
                'attr' => [
                    'class' => 'select2',
                ],
                'constraints' => [
                    new Callback([
                        'callback' => function ($data, ExecutionContextInterface $context): void {
                            /** @var FormInterface $form */
                            $form = $context->getObject();

                            if (null === $data && $form->getParent()->get('forcedValue')->getData()) {
                                $context
                                    ->buildViolation('app.bundle_generation.option_value_empty')
                                    ->addViolation()
                                ;
                            }
                        },
                        'groups' => ['sylius'],
                    ]),
                ],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BundleGeneratorOption::class,
            'validation_groups' => ['sylius'],
        ]);
    }
}
