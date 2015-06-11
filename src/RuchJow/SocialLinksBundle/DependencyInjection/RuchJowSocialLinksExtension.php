<?php

namespace RuchJow\SocialLinksBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RuchJowSocialLinksExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $services = $config['services'];
//        $services = array();
//        foreach ($config['services'] as $key => $definition) {
//            $services[$key] = array(
//                'service' => $key,
//                'pathPrefix' => $definition['path_prefix'],
//            );
//        }
        $container->setParameter('ruch_jow_social_links.services', $services);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}

