<?php

namespace App\View;

use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use Doctrine\Common\Collections\Collection;

class ProductOptionView
{
    public string $code;

    public string $name;

    public string $defaultOptionValue;

    /**
     * @var Collection|ProductOptionValueInterface[]
     */
    public mixed $values;
}
