<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 9/17/14
 * Time: 9:18 AM
 */

namespace RuchJow\SocialLinksBundle\Entity;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

class SocialLinkManager {

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SocialLinkRepository
     */
    protected $repository;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $socialServices;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function getEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->container->get('doctrine')->getManager();
        }

        return $this->entityManager;
    }

    protected function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->getEntityManager()->getRepository('RuchJowSocialLinksBundle:SocialLink');
        }

        return $this->repository;
    }

    public function getSocialServices()
    {
        if (!$this->socialServices) {
            $this->socialServices = $this->container->getParameter('ruch_jow_social_links.services');
        }

        return $this->socialServices;
    }


    /**
     * @param string $service
     * @param string $path
     * @param string &$errMsg
     *
     * @throws \InvalidArgumentException
     *
     * @return SocialLink|null
     */
    public function createSocialLink($service, $path, &$errMsg)
    {
        $socialLink = new SocialLink();

        $errMsg = $this->updateSocialLink($socialLink, $service, $path);

        return $errMsg ? null : $socialLink;
    }

    /**
     * @param SocialLink $socialLink
     * @param string     $service
     * @param string     $path
     *
     * @throws \InvalidArgumentException
     *
     * @return null|string
     */
    public function updateSocialLink(SocialLink $socialLink, $service, $path)
    {
        $services = $this->getSocialServices();

        if (!isset($services[$service])) {
            throw new \InvalidArgumentException('Service type ' . $service . ' is not defined.');
        }

        $serviceDef = $services[$service];

        if (
            isset($serviceDef['path_suffix_pattern'])
            && !preg_match($serviceDef['path_suffix_pattern'], $path)
        ) {
            return "Incorrect path suffix format.";
        }

        $socialLink
            ->setService($service)
            ->setPath($path)
            ->setFullPath($serviceDef['path_base'] . $path);

        return null;
    }

}