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
        $treeBuilder = new TreeBuilder('bi_core');
        $rootnode = $treeBuilder->getRootNode();

        $rootnode->children()
                ->scalarNode('lockfile')->defaultValue('%kernel.cache_dir%/maintenance.lock')->end()
                ->scalarNode('appname')->defaultValue('BiCoreBundle')->end()
                ->scalarNode('appid')->defaultValue('999')->end()
                ->scalarNode('table_prefix')->defaultValue('__bicorebundle_')->end()
                ->scalarNode('table_schema')->defaultValue('')->end()
                ->scalarNode('admin4test')->defaultValue('admin')->end()
                ->scalarNode('adminpwd4test')->defaultValue('admin')->end()
                ->scalarNode('usernoroles4test')->defaultValue('usernoroles')->end()
                ->scalarNode('usernorolespwd4test')->defaultValue('usernoroles')->end()
                ->scalarNode('userreadroles4test')->defaultValue('userreadroles')->end()
                ->scalarNode('userreadrolespwd4test')->defaultValue('userreadroles')->end()
                ->booleanNode('solosso')->defaultFalse()->end()
                ->end();

        return $treeBuilder;
    }
}
