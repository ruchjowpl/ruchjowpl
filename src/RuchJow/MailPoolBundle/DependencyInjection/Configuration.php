<?php

namespace RuchJow\MailPoolBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('ruch_jow_mail_pool');
        $rootNode
            ->children()
                ->scalarNode('default_from')
                    ->defaultNull()
                ->end()
                ->scalarNode('default_from_name')
                    ->defaultNull()
                ->end()
                ->scalarNode('default_reply_to')
                    ->defaultNull()
                ->end()
                ->scalarNode('delivery_address')
                    ->defaultNull()
                ->end()
                ->scalarNode('disable_delivery')
                    ->defaultFalse()
                ->end()
                ->arrayNode('mailgun')
                    ->children()
                        ->scalarNode('api_key')->end()
                        ->scalarNode('domain')->end()
                    ->end()
                ->end()
            ->end();


        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
