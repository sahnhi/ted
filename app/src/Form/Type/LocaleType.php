<?php

namespace App\Form\Type;

use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleType extends AbstractType
{
    public function __construct(
        private readonly LocaleProviderInterface $localProvider
    ) {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getLocalesCode(),
        ]);
    }

    /**
     * @return mixed[]
     */
    private function getLocalesCode(): array
    {
        $locales = [];
        $availableLocalesCodes = $this->localProvider->getAvailableLocalesCodes();

        foreach ($availableLocalesCodes as $locale) {
            $locales[$locale] = $locale;
        }

        return $locales;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
