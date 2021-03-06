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
                        ->scalarNode('host')->isRequired()->end()
                        ->scalarNode('response')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('seal')->canBeEnabled()
                    ->children()
                        ->scalarNode('private')->isRequired()->end()
                        ->scalarNode('public')->isRequired()->end()
                        ->scalarNode('response')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('guzzle_middleware')->canBeEnabled()
                    ->children()
                        ->booleanNode('unseal')->defaultFalse()->end()
                        ->booleanNode('verify')->defaultFalse()->end()
                        ->booleanNode('sign_request')->defaultFalse()->end()
                        ->booleanNode('seal_request')->defaultFalse()->end()
                        ->scalarNode('requester_host')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('sealing_public_keys')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('key')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('verifying_public_keys')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('key')->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('verify_request')->defaultFalse()->end()
                ->booleanNode('unseal_request')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
