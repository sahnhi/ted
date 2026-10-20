<?php

namespace App\DependencyInjection;

use App\Registry\EmailConfigurationRegistry;
use App\Registry\EmailConfigurationRegistryInterface;
use App\Registry\FtpConfigurationRegistry;
use App\Registry\FtpConfigurationRegistryInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

class TediberExtension extends Extension
{
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new TediberConfiguration();
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new TediberConfiguration(), $configs);

        if (array_key_exists('ftp', $config)) {
            $this->loadFtpConfigurations($config['ftp'], $container);
        }

        if (array_key_exists('email', $config)) {
            $this->loadEmailConfiguration($config['email'], $container);
        }
    }

    private function loadFtpConfigurations(array $ftpConfigurations, ContainerBuilder $container): void
    {
        $registryDefinition = new Definition(FtpConfigurationRegistry::class);

        foreach ($ftpConfigurations as $name => $ftpConfiguration) {
            $registryDefinition->addMethodCall('addFtpConfiguration', [
                $name, $ftpConfiguration,
            ]);
        }

        $container->setDefinition(FtpConfigurationRegistry::class, $registryDefinition);
        $container->setAlias(FtpConfigurationRegistryInterface::class, FtpConfigurationRegistry::class);
    }

    private function loadEmailConfiguration(array $emailConfigurations, ContainerBuilder $container): void
    {
        $registryDefinition = new Definition(EmailConfigurationRegistry::class);

        foreach ($emailConfigurations as $name => $emailConfiguration) {
            $registryDefinition->addMethodCall('addEmailConfiguration', [
                $name, $emailConfiguration,
            ]);
        }

        $container->setDefinition(EmailConfigurationRegistry::class, $registryDefinition);
        $container->setAlias(EmailConfigurationRegistryInterface::class, EmailConfigurationRegistry::class);
    }
}
