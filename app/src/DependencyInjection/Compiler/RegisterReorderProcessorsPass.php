<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterReorderProcessorsPass implements CompilerPassInterface
{
    private readonly string $serviceId;
    private readonly string $compositeId;
    private readonly string $tagName;
    private readonly string $methodName;

    public function __construct()
    {
        $this->serviceId = 'app.reorder_processing';
        $this->compositeId = \App\Component\Order\Processor\CompositeReorderProcessor::class;
        $this->tagName = 'app.reorder_processor';
        $this->methodName = 'addProcessor';
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has($this->compositeId)) {
            return;
        }

        $this->injectTaggedServicesIntoComposite($container);
        $this->addAliasForCompositeIfServiceDoesNotExist($container);
    }

    private function injectTaggedServicesIntoComposite(ContainerBuilder $container): void
    {
        $contextDefinition = $container->findDefinition($this->compositeId);

        $taggedServices = $container->findTaggedServiceIds($this->tagName);
        foreach ($taggedServices as $id => $tags) {
            $this->addMethodCalls($contextDefinition, $id, $tags);
        }
    }

    private function addAliasForCompositeIfServiceDoesNotExist(ContainerBuilder $container): void
    {
        if ($container->has($this->serviceId)) {
            return;
        }

        $container->setAlias($this->serviceId, $this->compositeId)->setPublic(true);
    }

    private function addMethodCalls(Definition $contextDefinition, string $id, array $tags): void
    {
        foreach ($tags as $attributes) {
            $this->addMethodCall($contextDefinition, $id, $attributes);
        }
    }

    private function addMethodCall(Definition $contextDefinition, string $id, array $attributes): void
    {
        /* Custom Code */
        $attributes['priority'] ??= 0;
        array_unshift($attributes, new Reference($id));

        $contextDefinition->addMethodCall($this->methodName, array_values($attributes));
        /* End Custom Code */
    }
}
