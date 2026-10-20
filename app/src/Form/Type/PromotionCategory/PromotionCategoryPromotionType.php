<?php

namespace App\Form\Type\PromotionCategory;

use App\Doctrine\Entity\PromotionCategoryPromotions;
use App\Doctrine\Entity\Sylius\Promotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionCategoryPromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('priority', IntegerType::class, ['label' => 'sylius.form.promotion_category.priority'])
            ->add('promotion', EntityType::class, [
                'label' => 'Promotion',
                'class' => Promotion::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_promotion_category_promotion';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PromotionCategoryPromotions::class]);
    }
}
