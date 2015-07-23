<?php

namespace RuchJow\BackendBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RuchJowBackendExtension extends Extension
{

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        // Roles.
        $roles = array();
        foreach ($config['exposed_user_roles'] as $role) {
            $roles[$role] = $role;
        }
        $container->setParameter('ruch_jow_backend.exposed_user_roles', $roles);

        $container->setParameter('ruch_jow_backend.user.search.max_elements',
            $config['user']['search']['max_elements']);
        $container->setParameter('ruch_jow_backend.user.search.min_length',
            $config['user']['search']['min_length']);
        $container->setParameter('ruch_jow_backend.user.search.min_length_All',
            $config['user']['search']['min_length_all']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}

