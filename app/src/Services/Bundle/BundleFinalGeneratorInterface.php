<?php

namespace App\Services\Bundle;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

interface BundleFinalGeneratorInterface
{
    public function generate(ResourceControllerEvent $event): void;
}
