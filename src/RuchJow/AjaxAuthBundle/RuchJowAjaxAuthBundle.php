<?php

namespace RuchJow\AjaxAuthBundle;

use RuchJow\AjaxAuthBundle\DependencyInjection\Security\Factory\FacebookFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RuchJowAjaxAuthBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new FacebookFactory());
    }
}
