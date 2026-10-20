<?php

namespace App\Services\Drip;

use App\Services\Channel\ChannelCode;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DripUrlBuilder
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly string $token,
        private readonly LocaleContextInterface $localeContext
    ) {
    }

    public function buildUrl(string $liquidShortCode): string
    {
        return urldecode($this->router->generate('shop_api_promotion_lingot_drip', [
            'email' => '{{subscriber.email}}',
            'token' => $this->token,
            'lingot' => $liquidShortCode,
            'channelCode' => ChannelCode::CHANNEL_WEB,
            'localeCode' => $this->localeContext->getLocaleCode(),
        ], UrlGeneratorInterface::ABSOLUTE_URL));
    }
}
