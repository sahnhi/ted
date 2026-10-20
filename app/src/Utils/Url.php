<?php

declare(strict_types=1);

namespace App\Utils;

use Assert\Assertion;
use Assert\AssertionFailedException;

final class Url implements \Stringable
{
    private readonly string $url;

    /**
     * @throws AssertionFailedException
     */
    public function __construct(string $url)
    {
        Assertion::url($url);
        $this->url = $url;
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
