<?php

namespace RuchJow\UserBundle\DataFixtures\ORM;

use RuchJow\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RuchJow\UserBundle\Entity\User;

class LoadUsersData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

    private function makeUser($username, $passwordText, $email, $firstName, $lastName, $roles, $commune = null, $organisation = null)
    {
        /** @var UserManager $manager */
        $manager = $this->container->get('ruch_jow_user.user_manager');

        /** @var User $user */
        $user = $manager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setFirstname($firstName);
        $user->setLastname($lastName);
        $user->setRoles((array) $roles);
        $user->setEnabled(true);
//        $user->setSupports(true);
        $user->setPlainPassword($passwordText);

//        $manager->confirmRegistration($user);

        if ($commune) {
            $user->setCommune($commune);
        }

        if ($organisation) {
            $user->setOrganisation($organisation);
        }

        $manager->updateUser($user);

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->makeUser('moderator', 'xlUZhD6ub2JT69c', 'spolecznoscjow@gmail.com',
            'Moderator', 'Moderujący', 'ROLE_MODERATOR');
   }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 13; // the order in which fixtures will be loaded
    }
}