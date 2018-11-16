<?php

namespace RevisionTen\Cleverreach\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        // Todo: https://symfony.com/blog/new-in-symfony-4-2-important-deprecations?#deprecated-tree-builders-without-root-nodes
        $rootNode = $treeBuilder->root('cleverreach');
        $rootNode
            ->children()
                ->scalarNode('client_id')->end()
                ->scalarNode('user')->end()
                ->scalarNode('password')->end()
                ->arrayNode('campaigns')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('list_id')->end()
                            ->scalarNode('form_id')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
