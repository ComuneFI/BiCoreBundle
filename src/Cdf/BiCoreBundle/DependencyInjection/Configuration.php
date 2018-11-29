<?php

namespace Cdf\BiCoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bi_core');
        $rootNode
                ->children()
                ->scalarNode('lockfile')->defaultValue('%kernel.cache_dir%/maintenance.lock')->end()
                ->scalarNode('appname')->defaultValue('BiCoreBundle')->end()
                ->scalarNode('appid')->defaultValue('999')->end()
                ->booleanNode('solosso')->defaultFalse()->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
