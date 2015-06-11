<?php

namespace RuchJow\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RuchJowUserExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // User validation configuration.
        $userValidations = array();
        foreach ($config['user_validation'] as $key => $definition) {
            $key = lcfirst(implode('', array_map('ucfirst', explode('_', $key))));
            $userValidations[$key] = array();
            foreach ($definition as $name => $value) {
                // Camelize.
                $name = lcfirst(implode('', array_map('ucfirst', explode('_', $name))));
                $userValidations[$key][$name] = $value;
            }
        }
        $container->setParameter('ruch_jow_user.user_validation', $userValidations);

        // Registration mail configuration.
        $container->setParameter(
            'ruch_jow_user.registration.confirmation_email.subject',
            $config['confirmation_email']['subject']
        );
        $container->setParameter(
            'ruch_jow_user.registration.confirmation_email.from',
            $config['confirmation_email']['from']
        );
        $container->setParameter(
            'ruch_jow_user.registration.confirmation_email.from_name',
            $config['confirmation_email']['from_name']
        );

        // Password reset mail configuration.
        $container->setParameter(
            'ruch_jow_user.password_reset.email.subject',
            $config['password_reset']['email']['subject']
        );
        $container->setParameter(
            'ruch_jow_user.password_reset.email.from',
            $config['password_reset']['email']['from']
        );
        $container->setParameter(
            'ruch_jow_user.password_reset.email.from_name',
            $config['password_reset']['email']['from_name']
        );

        // Invitation mail configuration.
        $container->setParameter(
            'ruch_jow_user.invitation.email.subject',
            $config['invitation']['email']['subject']
        );
        $container->setParameter(
            'ruch_jow_user.invitation.email.from',
            $config['invitation']['email']['from']
        );
        $container->setParameter(
            'ruch_jow_user.invitation.email.from_name',
            $config['invitation']['email']['from_name']
        );

        // Thanks mail configuration.
        $container->setParameter(
            'ruch_jow_user.thanks.email.subject',
            $config['thanks']['email']['subject']
        );
        $container->setParameter(
            'ruch_jow_user.thanks.email.from',
            $config['thanks']['email']['from']
        );
        $container->setParameter(
            'ruch_jow_user.thanks.email.from_name',
            $config['thanks']['email']['from_name']
        );
        $container->setParameter(
            'ruch_jow_user.thanks.email.time',
            $config['thanks']['email']['time']
        );

        // Reminder mail configuration.
        $container->setParameter(
            'ruch_jow_user.reminder.email.subject',
            $config['reminder']['email']['subject']
        );
        $container->setParameter(
            'ruch_jow_user.reminder.email.from',
            $config['reminder']['email']['from']
        );
        $container->setParameter(
            'ruch_jow_user.reminder.email.from_name',
            $config['reminder']['email']['from_name']
        );
        $container->setParameter(
            'ruch_jow_user.reminder.email.remainders',
            $config['reminder']['email']['remainders']
        );


        // TOKENS (prepare)
        $tokens = array();

        // Referral links
        $tokens['referral.link.token'] = $config['referral_links']['token_length'];

        // Password reset
        $tokens['password.reset.token'] = $config['password_reset']['token_length'];

        // TOKENS (set parameter)
        $container->setParameter(
            'ruch_jow_user.token_generator.tokens',
            $tokens
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}