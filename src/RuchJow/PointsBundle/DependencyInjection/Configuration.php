<?php

namespace RuchJow\PointsBundle\DependencyInjection;

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
            $treeBuilder->root('ruch_jow_points');
        $rootNode
            ->children()
                ->arrayNode('predefined_types')
//                    ->addDefaultsIfNotSet()
                    ->useAttributeAsKey('pointsType')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('points')
                                ->isRequired(true)
                            ->end()
                            ->scalarNode('comment')
//                                ->isRequired(true)
//                                ->defaultValue('exact')
//                                ->validate()
//                                    ->ifNotInArray(array('exact', 'minimum'))
//                                        ->thenInvalid('Type must be one of "exact" or "minimum".')
//                                    ->end()
//                                ->end()
                            ->end()
                            ->scalarNode('manual')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('min')
//                                ->isRequired(false)
//                                ->defaultNull()
                            ->end()
                            ->scalarNode('max')
//                                ->isRequired(false)
//                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;


        return $treeBuilder;
    }
}
