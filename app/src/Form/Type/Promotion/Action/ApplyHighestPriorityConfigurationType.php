<?php

declare(strict_types=1);

namespace App\Form\Type\Promotion\Action;

use App\Doctrine\Entity\Sylius\Promotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class ApplyHighestPriorityConfigurationType extends AbstractType
{
    public function __construct(
        private readonly DataTransformerInterface $promotionsToCodesTransformer
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('promotions', EntityType::class, [
                'multiple' => true,
                'class' => Promotion::class,
                'choice_value' => 'code',
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'ui fluid multiple search selection dropdown',
                ],
            ]);

        $builder->get('promotions')->addModelTransformer($this->promotionsToCodesTransformer);
    }
}
