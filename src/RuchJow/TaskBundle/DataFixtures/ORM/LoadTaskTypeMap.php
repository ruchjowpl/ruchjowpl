<?php

namespace RuchJow\TaskBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use RuchJow\TaskBundle\Entity\TaskTypeMap;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserRepository;

class LoadTaskTypeMApData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $taskMapData = array(
            array(
                'username' => 'moderator',
                'type'     => TaskTypeMap::TYPE_ALL
            )
        );


        /** @var UserRepository $userRepo */
        $userRepo = $manager->getRepository('RuchJowUserBundle:User');

        foreach ($taskMapData as $definition) {

            /** @var User $user */
            $user = $userRepo->findOneBy(array('username' => $definition['username']));

            if (!$user) {
                echo 'User ' . $definition['username'] . ' not found!' . "\n";

                continue;
            }

            $taskTypeMap = new TaskTypeMap();
            $taskTypeMap
                ->setUser($user)
                ->setType($definition['type']);

            $manager->persist($taskTypeMap);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 20; // the order in which fixtures will be loaded
    }
}