<?php

namespace App\Services\Bundle;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

interface BundleProductsGeneratorInterface
{
    public function generateProducts(ResourceControllerEvent $event): void;

    public function generateVariants(ResourceControllerEvent $event): void;
}
