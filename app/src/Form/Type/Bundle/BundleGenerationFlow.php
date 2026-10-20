<?php

namespace App\Form\Type\Bundle;

use App\Form\Type\Bundle\FormSubscriber\AddNamingStepFormSubscriber;
use App\Form\Type\Bundle\FormSubscriber\AddOptionsStepFormSubscriber;
use App\Form\Type\Bundle\FormSubscriber\AddPreviewStepFormSubscriber;
use App\Form\Type\Bundle\FormSubscriber\AddProductsStepFormSubscriber;
use App\Form\Type\Bundle\FormSubscriber\AddVariantsStepFormSubscriber;
use Craue\FormFlowBundle\Form\FormFlow;

class BundleGenerationFlow extends FormFlow
{
    protected $allowDynamicStepNavigation = true;

    protected $revalidatePreviousSteps = false;

    protected function loadStepsConfig(): array
    {
        $stepNames = [
            AddOptionsStepFormSubscriber::BUNDLE_GENERATION_STEP,
            AddPreviewStepFormSubscriber::BUNDLE_GENERATION_STEP,
            AddNamingStepFormSubscriber::BUNDLE_GENERATION_STEP,
            AddProductsStepFormSubscriber::BUNDLE_GENERATION_STEP,
            AddVariantsStepFormSubscriber::BUNDLE_GENERATION_STEP,
        ];

        $steps = [];

        foreach ($stepNames as $stepName) {
            $steps[] = [
                'label' => $stepName,
                'form_type' => BundleConfigurationType::class,
                'form_options' => [
                    'step' => $stepName,
                    'validation_groups' => ['sylius'],
                ],
            ];
        }

        return $steps;
    }

    protected function applyDataFromSavedSteps()
    {
        // Prevent default
    }
}
