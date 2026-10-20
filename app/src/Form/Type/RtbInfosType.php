<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class RtbInfosType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'translations',
                ResourceTranslationsType::class,
                [
                    'label' => 'app.form.rtb_infos.translations',
                    'entry_type' => RtbInfosTranslationType::class,
                ]
            )
            ->add(
                'priority',
                IntegerType::class,
                [
                    'label' => 'app.form.rtb.priority',
                    'required' => false,
                ]
            )
            ->add(
                'imageUrl',
                UrlType::class,
                [
                    'label' => 'app.form.rtb.image_url',
                    'required' => false,
                ]
            );
    }
}
