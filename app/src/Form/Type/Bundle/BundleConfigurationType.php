<?php

namespace App\Form\Type\Bundle;

use App\Doctrine\Entity\BundleGenerator;
use App\Form\Type\Bundle\FormSubscriber\AddNamingStepFormSubscriber;
use App\Form\Type\Bundle\FormSubscriber\AddOptionsStepFormSubscriber;
use App\Form\Type\Bundle\FormSubscriber\AddPreviewStepFormSubscriber;
use App\Form\Type\Bundle\FormSubscriber\AddProductsStepFormSubscriber;
use App\Form\Type\Bundle\FormSubscriber\AddVariantsStepFormSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BundleConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        switch ($options['step']) {
            case AddVariantsStepFormSubscriber::BUNDLE_GENERATION_STEP:
                $builder->addEventSubscriber(new AddVariantsStepFormSubscriber());
                break;
            case AddProductsStepFormSubscriber::BUNDLE_GENERATION_STEP:
                $builder->addEventSubscriber(new AddProductsStepFormSubscriber());
                break;
            case AddNamingStepFormSubscriber::BUNDLE_GENERATION_STEP:
                $builder->addEventSubscriber(new AddOptionsStepFormSubscriber());
                $builder->addEventSubscriber(new AddNamingStepFormSubscriber());
                break;
            case AddPreviewStepFormSubscriber::BUNDLE_GENERATION_STEP:
                $builder->addEventSubscriber(new AddPreviewStepFormSubscriber());
                break;
            case AddOptionsStepFormSubscriber::BUNDLE_GENERATION_STEP:
                $builder->addEventSubscriber(new AddOptionsStepFormSubscriber());
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('step');
        $resolver->setDefaults([
            'data_class' => BundleGenerator::class,
        ]);
    }
}
