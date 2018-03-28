<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('sapient');

        $rootNode
            ->children()
                ->arrayNode('sign')->canBeEnabled()
                    ->children()
                        ->scalarNode('private')->isRequired()->end()
                        ->scalarNode('public')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('seal')->canBeEnabled()
                    ->children()
                        ->scalarNode('private')->isRequired()->end()
                        ->scalarNode('public')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('requester_public_keys')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('origin')->end()
                            ->scalarNode('key')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
