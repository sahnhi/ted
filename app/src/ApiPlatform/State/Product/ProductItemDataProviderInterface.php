<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Product;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;

/**
 * This interface is here only to facilitate testing since PHPUnit supports type intersection only from version 10.
 */
interface ProductItemDataProviderInterface extends RestrictedDataProviderInterface, ItemDataProviderInterface
{
}
