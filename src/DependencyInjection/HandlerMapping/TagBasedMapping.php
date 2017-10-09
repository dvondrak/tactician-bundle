<?php
declare(strict_types=1);

namespace League\Tactician\Bundle\DependencyInjection\HandlerMapping;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

abstract class TagBasedMapping implements HandlerMapping
{
    public function build(ContainerBuilder $container, Routing $routing): Routing
    {
        foreach ($container->findTaggedServiceIds('tactician.handler') as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $this->mapServiceByTag($container, $routing, $serviceId, $attributes);
            }
        }

        return $routing;
    }

    /**
     * @param ContainerBuilder $container
     * @param Routing $routing
     * @param $serviceId
     * @param $attributes
     */
    private function mapServiceByTag(ContainerBuilder $container, Routing $routing, $serviceId, $attributes)
    {
        $definition = $container->getDefinition($serviceId);

        if (!$this->isSupported($definition, $attributes)) {
            return;
        }

        foreach ($this->findCommandsForService($definition, $attributes) as $commandClassName) {
            if (isset($attributes['bus'])) {
                $routing->routeToBus($attributes['bus'], $commandClassName, $serviceId);
            } else {
                $routing->routeToAllBuses($commandClassName, $serviceId);
            }
        }
    }

    abstract protected function isSupported(Definition $definition, array $tagAttributes): bool;

    abstract protected function findCommandsForService(Definition $definition, array $tagAttributes): array;
}
