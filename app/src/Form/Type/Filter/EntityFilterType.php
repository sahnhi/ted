<?php

namespace App\Form\Type\Filter;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityFilterType extends AbstractType
{
    public function __construct(
        private readonly LocaleContextInterface $localeContext
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'class' => null,
                'label' => false,
                'placeholder' => 'sylius.ui.all',
                'method' => null,
                'needLocale' => false,
                'choices' => function (Options $options) {
                    if (null !== ($method = $options['method'])) {
                        return $options['needLocale'] ?
                            $options['em']->getRepository($options['class'])->$method($this->localeContext->getLocaleCode()) :
                            $options['em']->getRepository($options['class'])->$method();
                    }

                    return null;
                },
            ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_grid_filter_entity';
    }
}
