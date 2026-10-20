<?php

namespace App\View;

final class CountryView implements \Stringable
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $paidReturn;

    public function __toString(): string
    {
        return $this->name;
    }
}
