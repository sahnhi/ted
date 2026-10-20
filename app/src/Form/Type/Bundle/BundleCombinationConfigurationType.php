<?php

namespace App\Form\Type\Bundle;

use App\Doctrine\Entity\BundleGeneratorCombination;
use App\Doctrine\Entity\BundleGeneratorCombinationInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BundleCombinationConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('optionValues', EntityType::class, [
                'multiple' => true,
                'class' => ProductOptionValue::class,
            ])
            ->add('alreadyExists', CheckboxType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            /** @var BundleGeneratorCombinationInterface $data */
            $data = $event->getData();
            $form = $event->getForm();

            $form->add('willBeGenerated', CheckboxType::class, [
                'required' => false,
                'disabled' => $data->isAlreadyExists(),
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BundleGeneratorCombination::class,
            'validation_groups' => ['sylius'],
        ]);
    }
}
