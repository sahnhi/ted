<?php

namespace App\ViewRepository;

use Sylius\Bundle\ChannelBundle\Doctrine\ORM\ChannelRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Webmozart\Assert\Assert;

abstract class AbstractViewRepository
{
    /** @var ChannelRepository */
    protected $channelRepository;

    public function setChannelRepository(ChannelRepository $channelRepository): void
    {
        $this->channelRepository = $channelRepository;
    }

    protected function getChannel(string $channelCode): ChannelInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->findOneByCode($channelCode);
        Assert::notNull($channel, sprintf('Channel with code %s has not been found.', $channelCode));

        return $channel;
    }

    protected function getLocaleCode(ChannelInterface $channel): string
    {
        $localeCode = $channel->getDefaultLocale()->getCode();
        $this->assertLocaleSupport($localeCode, $channel->getLocales());

        return $localeCode;
    }

    protected function assertLocaleSupport(string $localeCode, iterable $supportedLocales): void
    {
        $supportedLocaleCodes = [];
        foreach ($supportedLocales as $locale) {
            $supportedLocaleCodes[] = $locale->getCode();
        }

        Assert::oneOf($localeCode, $supportedLocaleCodes);
    }
}
