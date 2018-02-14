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
        $rootNode = $treeBuilder->root('greg_doak_cron');

        $rootNode
            ->children()
            ->booleanNode('run_on_request')->defaultFalse()->end()
            ->end();

        return $treeBuilder;
    }
}
