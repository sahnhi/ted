<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class RtbInfosTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'label',
                TextType::class,
                [
                    'label' => 'app.form.rtb.label',
                ]
            )
            ->add(
                'description',
                TextType::class,
                [
                    'label' => 'app.form.rtb.description',
                    'required' => false,
                ]
            )
            ->add(
                'url',
                UrlType::class,
                [
                    'label' => 'app.form.rtb.url',
                    'required' => false,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_rtb_infos_translation';
    }
}
