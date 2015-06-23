<?php

namespace RuchJow\AddressBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('ruch_jow_address');
        $rootNode
            ->children()
                ->arrayNode('validation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('firstName')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('pattern')
                                    ->defaultValue('/^([A-ZĄĆĘŁŃÓŚŹŻ]|[a-ząćęłńóśźż]|[.0-9_\-+()*&%$#@!?,;:"]){2,}( ?([A-ZĄĆĘŁŃÓŚŹŻ]|[a-ząćęłńóśźż]|[ .0-9_\-+()*&%$#@!?,;:"]))*$/')
                                ->end()
                                ->scalarNode('pattern_error')
                                    ->defaultValue('Firstname must contain only letters and special characters . 0 - 9 _ - + = ( ) * & % $ # @ ! ? , ; : ". Also it must be at least two letters long.')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('lastName')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('pattern')
                                    ->defaultValue('/^([A-ZĄĆĘŁŃÓŚŹŻ]|[a-ząćęłńóśźż]|[.0-9_\-+()*&%$#@!?,;:"]){2,}( ?([A-ZĄĆĘŁŃÓŚŹŻ]|[a-ząćęłńóśźż]|[ .0-9_\-+()*&%$#@!?,;:"]))*$/')
                                ->end()
                                ->scalarNode('pattern_error')
                                    ->defaultValue('Lastname must contain only letters and special characters . 0 - 9 _ - + = ( ) * & % $ # @ ! ? , ; : ". Also it must be at least two letters long.')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('street')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('pattern')
                                    ->defaultValue('/^[0-9a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ+=()*&%$#@!?,;: "\/-]{2,}/')
                                ->end()
                                ->scalarNode('pattern_error')
                                    ->defaultValue('Street must contain only letters and special characters . 0 - 9 _ - + = ( ) * & % $ # @ ! ? , ; : ". Also it must be at least two letters long.')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('house')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('pattern')
                                    ->defaultValue('/^[0-9a-zA-Z\/-]+$/')
                                ->end()
                                ->scalarNode('pattern_error')
                                    ->defaultValue('House number must consist of numbers, letters and special characters "/", "-" only.')
                                ->end()
                                ->scalarNode('required')
                                    ->defaultTrue()
                                ->end()
                                ->scalarNode('required_error')
                                    ->defaultValue('House number is required.')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('flat')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('pattern')
                                    ->defaultValue('/^[0-9a-zA-Z\/-]+$/')
                                ->end()
                                ->scalarNode('pattern_error')
                                    ->defaultValue('Flat number must consist of numbers, letters and special characters "/", "-" only.')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('post_code')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('pattern')
                                    ->defaultValue('/^\d\d[ -.]?\d\d\d$/')
                                ->end()
                                ->scalarNode('pattern_error')
                                    ->defaultValue('Post code should be in format 54-321.')
                                ->end()
                                ->scalarNode('required')
                                    ->defaultTrue()
                                ->end()
                                ->scalarNode('required_error')
                                    ->defaultValue('Post code is required.')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('city')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('pattern')
                                    ->defaultValue('/^([A-ZĄĆĘŁŃÓŚŹŻ]|[a-ząćęłńóśźż]){2,}([ -]?([A-ZĄĆĘŁŃÓŚŹŻ]|[a-ząćęłńóśźż]))*$/')
                                ->end()
                                ->scalarNode('pattern_error')
                                    ->defaultValue('City may contain letters, spaces and hyphen only.')
                                ->end()
                                ->scalarNode('required')
                                    ->defaultTrue()
                                ->end()
                                ->scalarNode('required_error')
                                    ->defaultValue('City is required.')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;


        return $treeBuilder;
    }
}
