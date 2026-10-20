<?php

namespace App\Services\Bundle;

use App\Doctrine\Entity\Sylius\ProductInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

interface BundleCombinationGeneratorInterface
{
    public function generateOptions(ResourceControllerEvent $event): void;

    public function doesCombinationAlreadyExists(ProductInterface $product, array $optionsValues): bool;
}
