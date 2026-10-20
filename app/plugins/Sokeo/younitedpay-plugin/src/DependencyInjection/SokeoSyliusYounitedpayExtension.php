<?php

/*
 * This file was created by developers working at Sokeo
 * Do you need more information about us and what we do? Visit our https://sokeo.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://sokeo.io/career
*/

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SokeoSyliusYounitedpayExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');
    }
}
