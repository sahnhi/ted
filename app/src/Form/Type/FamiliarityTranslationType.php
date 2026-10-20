<?php

namespace App\Form\Type;

use App\Doctrine\Entity\FamiliarityTranslation;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FamiliarityTranslationType extends AbstractResourceType
{
    public function __construct()
    {
        parent::__construct(FamiliarityTranslation::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('wording', TextType::class, [
                'label' => 'sylius.form.familiarity.wording',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'sylius_familiarity_translation';
    }
}
