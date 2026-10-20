<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Familiarity;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class FamiliarityType extends AbstractResourceType
{
    public function __construct()
    {
        parent::__construct(Familiarity::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'sylius.form.familiarity.code',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => FamiliarityTranslationType::class,
                'label' => 'sylius.form.familiarity.translations',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_familiarity';
    }
}
