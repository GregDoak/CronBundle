<?php

namespace GregDoak\CronBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package GregDoak\CronBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('greg_doak');

        $rootNode
            ->children()
            ->arrayNode('cron')
            ->children()
            ->scalarNode('run_on_request')
            ->defaultValue(false)
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
