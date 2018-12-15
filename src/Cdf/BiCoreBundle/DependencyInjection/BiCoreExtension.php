<?php

namespace Cdf\BiCoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BiCoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $container->setParameter('bi_core.lockfile', $config['lockfile']);
        $container->setParameter('bi_core.appname', $config['appname']);
        $container->setParameter('bi_core.appid', $config['appid']);
        $container->setParameter('bi_core.solosso', $config['solosso']);

        $container->setParameter('bi_core.table_prefix', $config['table_prefix']);
        $container->setParameter('bi_core.table_schema', $config['table_schema']);

        $container->setParameter('bi_core.admin4test', $config['admin4test']);
        $container->setParameter('bi_core.adminpwd4test', $config['adminpwd4test']);

        $container->setParameter('bi_core.usernoroles4test', $config['usernoroles4test']);
        $container->setParameter('bi_core.usernorolespwd4test', $config['usernorolespwd4test']);

        $container->setParameter('bi_core.userreadroles4test', $config['userreadroles4test']);
        $container->setParameter('bi_core.userreadrolespwd4test', $config['userreadrolespwd4test']);
    }
}
