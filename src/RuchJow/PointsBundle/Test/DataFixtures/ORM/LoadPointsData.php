<?php

namespace RuchJow\PointsBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPointsData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $pointsManager = $this->container->get('ruch_jow_points.points_manager');
        $users = $manager->getRepository('RuchJowUserBundle:User')->findAll();

        // everyone gets initial points for supporting the action
        foreach ($users as $user) {
            if ($user->getCommune()) {
                $pointsManager->addPoints($user, 'user.support');
                if (rand(0, 3) < 2) {
                    $pointsManager->addPoints($user, 'user.referral');
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 15; // the order in which fixtures will be loaded
    }
}