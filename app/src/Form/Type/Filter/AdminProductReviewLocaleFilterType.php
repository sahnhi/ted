<?php

namespace App\Form\Type\Filter;

use Doctrine\ORM\EntityRepository;
use Sylius\Component\Locale\Model\Locale;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminProductReviewLocaleFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->getName(), EntityType::class, [
                'label' => 'app.form.product_review.' . $builder->getName(),
                'class' => Locale::class,
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('l')
                    ->orderBy('l.code', 'ASC'),
                'choice_label' => 'code',
                'choice_value' => fn (?Locale $entity = null): ?string => $entity ? $entity->getCode() : '',
                'placeholder' => 'Tous',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'locale' => [],
            ])
            ->setAllowedTypes('locale', ['array'])
        ;
    }
}
