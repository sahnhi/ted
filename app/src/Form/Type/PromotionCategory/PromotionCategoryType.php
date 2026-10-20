<?php

namespace App\Form\Type\PromotionCategory;

use App\Doctrine\Entity\PromotionCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, ['label' => 'sylius.ui.enable'])
            ->add('code', TextType::class, ['label' => 'sylius.ui.code'])
            ->add('name', TextType::class, ['label' => 'sylius.ui.name'])
            ->add('description', TextareaType::class, [
                'label' => 'sylius.ui.description',
                'required' => false,
            ])
            ->add('promotionCategoryPromotions', CollectionType::class, [
                'label' => 'sylius.form.promotions',
                'entry_type' => PromotionCategoryPromotionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_promotion_category';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PromotionCategory::class]);
    }
}
