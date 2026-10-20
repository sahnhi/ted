<?php

namespace App\View;

class ProductOptionValueView
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $help;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $optgroup;

    /**
     * @var int
     */
    public $difference;

    public array $monobloc;

    public array $bibloc;

    public array $bedFramePriceDiff;
}
