<?php

namespace App\Business\Command;

use App\Model\GiftCardAddress;
use Webmozart\Assert\Assert;

final class GiftCardAddressOrder
{
    /**
     * @var string
     */
    private $orderToken;

    /**
     * @param string $orderToken
     */
    public function __construct(
        $orderToken,
        private readonly GiftCardAddress $giftCardAddress
    ) {
        Assert::string($orderToken);
        $this->orderToken = $orderToken;
    }

    /**
     * @return string
     */
    public function orderToken()
    {
        return $this->orderToken;
    }

    public function giftCardAddress(): GiftCardAddress
    {
        return $this->giftCardAddress;
    }
}
