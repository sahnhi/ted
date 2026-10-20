<?php

namespace App\Request;

use App\Business\Command\PickupCart;
use Symfony\Component\HttpFoundation\Request;

final class PickupCartRequest
{
    /**
     * @var string
     */
    private $channel;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var array
     */
    private $body;

    public function __construct(Request $request)
    {
        $this->channel = $request->attributes->get('channelCode');
        $this->locale = $request->attributes->get('localeCode');
        $this->body['version'] = $request->request->get('version') ?: null;
        $this->body['user-agent'] = $request->request->get('user-agent') ?: null;
    }

    /**
     * @throws \Exception
     */
    public function getCommand(): PickupCart
    {
        return new PickupCart($this->channel, $this->locale, $this->body);
    }
}
