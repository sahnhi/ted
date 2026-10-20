<?php

namespace App\Form\Type;

use App\Doctrine\Entity\PackTranslation;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PackTranslationType extends AbstractResourceType
{
    public function __construct()
    {
        parent::__construct(PackTranslation::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius.form.pack.name',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'sylius_pack_translation';
    }
}
