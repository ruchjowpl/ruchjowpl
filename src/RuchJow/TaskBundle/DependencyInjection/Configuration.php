<?php

namespace RuchJow\TaskBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode =
            $treeBuilder->root('ruch_jow_task');
        $rootNode
            ->children()
                ->scalarNode('default_status_name')
                    ->isRequired(true)
                ->end()
                ->arrayNode('mailer')
                    ->children('array')
                        ->scalarNode('from')
                            ->defaultNull()
                            ->isRequired(false)
                        ->end()
                        ->scalarNode('from_name')
                            ->defaultNull()
                            ->isRequired(false)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;


        return $treeBuilder;
    }
}
