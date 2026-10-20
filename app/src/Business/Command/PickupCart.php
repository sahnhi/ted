<?php

namespace App\Business\Command;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class PickupCart
{
    /**
     * @var string
     */
    private $orderToken;

    /**
     * @var string
     */
    private $channelCode;

    /**
     * @var array
     */
    private $body;

    /**
     * PickupCart constructor.
     *
     * @param string $localeCode
     *
     * @throws \Exception
     */
    public function __construct(
        $channelCode,
        private $localeCode,
        $body
    ) {
        Assert::string($channelCode, 'Expected channel code to be string, got %s');
        $this->orderToken = Uuid::uuid4()->toString();
        $this->channelCode = $channelCode;
        $this->body['version'] = $body['version'];
        $this->body['user-agent'] = $body['user-agent'];
    }

    /**
     * @return string
     */
    public function orderToken()
    {
        return $this->orderToken;
    }

    /**
     * @return string
     */
    public function channelCode()
    {
        return $this->channelCode;
    }

    /**
     * @return string
     */
    public function localeCode()
    {
        return $this->localeCode;
    }

    /**
     * @return array
     */
    public function body()
    {
        return $this->body;
    }

    public function hasFrontData(): bool
    {
        if (null !== $this->body['version'] || null !== $this->body['user-agent']) {
            return true;
        }

        return false;
    }
}
