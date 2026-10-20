<?php

namespace App\Services\Bundle;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

interface BundleCodeGeneratorInterface
{
    public function generateCodes(ResourceControllerEvent $event): void;
}
