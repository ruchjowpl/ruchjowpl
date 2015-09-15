<?php

namespace RuchJow\AjaxAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class FacebookFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {

        $userProviderId = 'ruch_jow_ajax_auth.user.provider.entity.'.$id;
        $container->setAlias($userProviderId, $config['user_provider']);


        $providerId = 'ruch_jow_ajax_auth.security.authentication.provider.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('ruch_jow_ajax_auth.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProviderId))
        ;

        $listenerId = 'ruch_jow_ajax_auth.security.authentication.listener.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('ruch_jow_ajax_auth.security.authentication.listener'))
            ->replaceArgument(2, $config['login_path']);

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'facebook';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $builder = $node->children();
        $builder
            ->scalarNode('user_provider')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('login_path')->cannotBeEmpty()->isRequired()->end();
    }
}