<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),

            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),

            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // libs bundles
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new cspoo\Swiftmailer\MailgunBundle\cspooSwiftmailerMailgunBundle(),

            // custom bundles
            new RuchJow\FacebookBundle\RuchJowFacebookBundle(),
            new RuchJow\AjaxAuthBundle\RuchJowAjaxAuthBundle(),
            new RuchJow\AngularSecurityBundle\RuchJowAngularSecurityBundle(),
            new RuchJow\JsonValidatorBundle\RuchJowJsonValidatorBundle(),
            new RuchJow\TransferujPlBundle\RuchJowTransferujPlBundle(),
            new RuchJow\MailPoolBundle\RuchJowMailPoolBundle(),
            new RuchJow\AppBundle\RuchJowAppBundle(),
            new RuchJow\PageFoundationBundle\RuchJowPageFoundationBundle(),
            new RuchJow\UserBundle\RuchJowUserBundle(),
            new RuchJow\TerritorialUnitsBundle\RuchJowTerritorialUnitsBundle(),
            new RuchJow\AddressBundle\RuchJowAddressBundle(),
            new RuchJow\SocialLinksBundle\RuchJowSocialLinksBundle(),
            new RuchJow\PointsBundle\RuchJowPointsBundle(),
            new RuchJow\MessagesBundle\RuchJowMessagesBundle(),
            new RuchJow\RanksBundle\RuchJowRanksBundle(),
            new RuchJow\StatisticsBundle\RuchJowStatisticsBundle(),
            new RuchJow\TaskBundle\RuchJowTaskBundle(),
            new RuchJow\FeedbackBundle\RuchJowFeedbackBundle(),
            new RuchJow\LocalGovBundle\RuchJowLocalGovBundle(),
            new RuchJow\FeedBundle\RuchJowFeedBundle(),

            // backend bundle
            new RuchJow\BackendBundle\RuchJowBackendBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Bazinga\Bundle\FakerBundle\BazingaFakerBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
