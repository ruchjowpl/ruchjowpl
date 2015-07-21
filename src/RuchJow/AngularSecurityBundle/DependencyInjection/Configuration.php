<?php

namespace RuchJow\AngularSecurityBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('ruch_jow_angular_security');
        $rootNode
            ->children()
                ->scalarNode('xsrf_cookie_name')
                    ->defaultValue('RJ_ANG_XSRF_TOKEN')
                ->end()
                ->scalarNode('xsrf_json_name')
                    ->defaultValue('ang_xsrf_token')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
