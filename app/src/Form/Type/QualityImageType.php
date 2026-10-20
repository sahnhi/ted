<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Quality;
use App\Doctrine\Entity\QualityImage;
use Sylius\Bundle\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class QualityImageType extends ImageType
{
    public function __construct(array $validationGroups = [])
    {
        parent::__construct(QualityImage::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'label' => 'sylius.form.attribute.name',
                'required' => false,
            ])
            ->add('file', FileType::class, [
                'label' => 'sylius.form.image.file',
            ])
            ->add('download', ButtonType::class, [
                'label' => 'sylius.form.image.download',
            ])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['quality'] = $options['quality'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefined('quality');
        $resolver->setAllowedTypes('quality', Quality::class);
    }

    public function getBlockPrefix(): string
    {
        return 'app_quality_image';
    }
}
