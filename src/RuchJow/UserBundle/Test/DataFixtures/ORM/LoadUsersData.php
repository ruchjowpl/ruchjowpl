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
//        $user->setEnabled(true);
//        $user->setSupports(true);
        $user->setPlainPassword($passwordText);

        $manager->confirmRegistration($user);

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
        $communeRepo = $manager->getRepository('RuchJowTerritorialUnitsBundle:Commune');
        $communes = $communeRepo->findAll(); // quite counter-efficient, but ORDER BY RAND() is not supported in DQL
        array_unshift($communes, $communeRepo->findOneByRegionDistrictCommuneType('dolnośląskie', 'Wrocław', 'Wrocław', 'gmina miejska'));

        $organisationRepo = $manager->getRepository('RuchJowUserBundle:Organisation');
        $organisations = $organisationRepo->findAll();

        $this->makeUser('admin', 'admin', 'admin@efektjow.pl',
            'Administrator', 'Wszechmocny', 'ROLE_ADMIN'
        );

        $this->makeUser('user', 'user', 'user@efektjow.pl',
            'Użytkownik', 'Serwisu', 'ROLE_USER', $communes[0]
        );

        $this->makeUser('tris', 'tris', 'tristran.thorn@stardust.pl',
            'Tristran', 'Thorn', 'ROLE_USER', $communes[0]
        );

        $used = array(
            'usernames' => array('admin', 'user', 'tris'),
            'emails' => array('admin@efektjow.pl', 'user@efektjow.pl', 'tristran.thorn@stardust.p'),
        );
        $faker = \Faker\Factory::create('pl_PL');

        for ($i = 1; $i <= 50; $i++)
        {
            if ($i % 10 === 0) {
                echo $i ."\n";
            }
            $organisation = $organisations[array_rand($organisations)];

            do {
                $username = $faker->userName;
            } while (in_array($username, $used['usernames']));
            $used['usernames'][] = $username;

            do {
                $email = $faker->email;
            } while (in_array($email, $used['emails']));
            $used['emails'][] = $email;

            $this->makeUser(
                $username,
                'test',
                $email,
                $faker->firstName,
                $faker->lastName,
                'ROLE_USER',
                $communes[array_rand($communes)],
                rand(0, 1) == 0 ? null : $organisation
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 13; // the order in which fixtures will be loaded
    }
}