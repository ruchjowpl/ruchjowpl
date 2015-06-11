<?php

namespace RuchJow\TaskBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use RuchJow\TaskBundle\Entity\TaskStatus;

class LoadTaskStatusData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $taskStatuses = array(
            array(
                'id'     => 'canceled',
                'name'   => 'anulowane',
                'new'    => false,
                'closed' => false,
                'canceled' => true,
            ),
            array(
                'id'     => 'new',
                'name'   => 'nowe',
                'new'    => true,
                'closed' => false,
                'canceled' => false,
            ),
            array(
                'id'     => 'read',
                'name'   => 'przeczytane',
                'new'    => false,
                'closed' => false,
                'canceled' => false,
            ),
            array(
                'id'     => 'closed',
                'name'   => 'zamknięte',
                'new'    => false,
                'closed' => true,
                'canceled' => false,
            ),
        );


        foreach ($taskStatuses as $definition) {
            $taskStatus = new TaskStatus();
            $taskStatus
                ->setId($definition['id'])
                ->setName($definition['name'])
                ->setNew($definition['new'])
                ->setClosed($definition['closed'])
                ->setCanceled($definition['canceled']);

            $manager->persist($taskStatus);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}