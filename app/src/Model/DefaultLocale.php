<?php

namespace App\Model;

use Sylius\Component\Locale\Context\LocaleContextInterface;

class DefaultLocale
{
    public function __construct(
        private readonly LocaleContextInterface $localeContext
    ) {
    }

    public function getLocale(): string
    {
        return $this->localeContext->getLocaleCode();
    }
}
