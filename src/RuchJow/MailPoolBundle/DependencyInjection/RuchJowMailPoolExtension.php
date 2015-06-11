<?php

namespace RuchJow\MailPoolBundle\DependencyInjection;

use RuchJow\TransferujPlBundle\Entity\TransferujPlUser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RuchJowMailPoolExtension extends Extension
{

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('ruch_jow_mail_pool.default_from', $config['default_from']);
        $container->setParameter('ruch_jow_mail_pool.default_from_name', $config['default_from_name']);
        $container->setParameter('ruch_jow_mail_pool.default_reply_to', $config['default_reply_to']);

        $container->setParameter('ruch_jow_mail_pool.delivery_address', $config['delivery_address']);
        $container->setParameter('ruch_jow_mail_pool.disable_delivery', $config['disable_delivery']);

        $container->setParameter('ruch_jow_mail_pool.mailgun.api_key', $config['mailgun']['api_key']);
        $container->setParameter('ruch_jow_mail_pool.mailgun.domain', $config['mailgun']['domain']);
    }
}

