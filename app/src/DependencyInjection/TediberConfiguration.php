<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Enum\EmailConfigurationName;
use App\Enum\FtpConfigurationName;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class TediberConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tediber');

        $rootNode = $treeBuilder->getRootNode();
        $this->addFtpSection($rootNode);
        $this->addEmailSection($rootNode);

        return $treeBuilder;
    }

    private function addFtpSection(ArrayNodeDefinition|NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('ftp')
            ->useAttributeAsKey('name')
            ->defaultValue([])
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
                        ->integerNode('port')->isRequired()->end()
                        ->scalarNode('user')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('password')->isRequired()->end()
                        ->booleanNode('has_private_key')->defaultFalse()->end()
                        ->scalarNode('directory')->defaultNull()->end()
                        ->scalarNode('tracking_path')->defaultNull()->end()
                        ->scalarNode('delivered_path')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(static fn (array $config): bool => array_key_exists('ftp', $config))
                ->then(static function (array $config): array {
                    $invalidFtpConfigNames = [];
                    foreach ($config['ftp'] as $ftpConfigName => $value) {
                        if (null === FtpConfigurationName::tryFrom($ftpConfigName)) {
                            $invalidFtpConfigNames[] = $ftpConfigName;
                        }
                    }

                    if ([] !== $invalidFtpConfigNames) {
                        throw new InvalidConfigurationException(sprintf('Unsupported FTP configuration: %s', implode(', ', $invalidFtpConfigNames)));
                    }

                    return $config;
                })
            ->end()
        ;
    }

    private function addEmailSection(ArrayNodeDefinition|NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('email')
            ->useAttributeAsKey('name')
            ->defaultValue([])
                ->arrayPrototype()
                    ->children()
                        ->arrayNode('principals')->isRequired()
                            ->scalarPrototype()
                        ->end()
                    ->end()
                    ->scalarNode('from')->end()
                    ->scalarNode('from_name')->end()
                    ->arrayNode('ccs')
                        ->scalarPrototype()
                    ->end()
                ->end()
            ->end()
            ->validate()
            ->ifTrue(static fn (array $config): bool => array_key_exists('email', $config))
            ->then(static function (array $config): array {
                $invalidEmailConfigNames = [];
                foreach ($config['email'] as $emailConfigName => $value) {
                    if (null === EmailConfigurationName::tryFrom($emailConfigName)) {
                        $invalidEmailConfigNames[] = $emailConfigName;
                    }
                }

                if ([] !== $invalidEmailConfigNames) {
                    throw new InvalidConfigurationException(sprintf('Unsupported EMAIL configuration: %s', implode(', ', $invalidEmailConfigNames)));
                }

                return $config;
            })
            ->end()
        ;
    }
}
