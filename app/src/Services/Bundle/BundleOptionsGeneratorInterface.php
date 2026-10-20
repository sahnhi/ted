<?php

namespace App\Services\Bundle;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

interface BundleOptionsGeneratorInterface
{
    public function generateOptions(ResourceControllerEvent $event): void;
}
