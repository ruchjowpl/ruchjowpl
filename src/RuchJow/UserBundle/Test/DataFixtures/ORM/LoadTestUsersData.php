<?php

namespace RuchJow\UserBundle\DataFixtures\ORM;

use RuchJow\TerritorialUnitsBundle\Entity\CommuneRepository;
use RuchJow\TerritorialUnitsBundle\Entity\Country;
use RuchJow\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RuchJow\UserBundle\Entity\User;
use Faker\Factory as FakerFactory;

class LoadTestUsersData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

    private function makeUser(
        $username,
        $email,
        $firstName,
        $lastName,
        $roles,
        $passwordText = null,
        $country,
        $commune = null,
        $organisation = null,
        $registered = true
    )
    {
        /** @var UserManager $manager */
        $manager = $this->container->get('ruch_jow_user.user_manager');

        /** @var User $user */
        $user = $manager->createUser();
        $user
            ->setUsername($username)
            ->setEmail($email)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setRoles((array) $roles)
            ->setEnabled(true);

        if ($passwordText) {
            $user->setPlainPassword($passwordText);
        } else {
            $user->setPassword('')
                ->setLocked(true);
        }
        $user->setCountry($country);
        if ($commune) {
            $user->setCommune($commune);
        }

        if ($organisation) {
            $user->setOrganisation($organisation);
        }

        if ($registered) {
            $manager->confirmRegistration($user);
        }

        $manager->updateUser($user);

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $country = $manager->getRepository('RuchJowTerritorialUnitsBundle:Country')->findOneByCode(Country::MAIN_COUNTRY);
        /** @var CommuneRepository $communeRepo */
        $communeRepo = $manager->getRepository('RuchJowTerritorialUnitsBundle:Commune');
        $communes = $communeRepo->findAll(); // quite counter-efficient, but ORDER BY RAND() is not supported in DQL
        array_unshift($communes, $communeRepo->findOneByRegionDistrictCommuneType('dolnośląskie', 'Wrocław', 'Wrocław', 'gmina miejska'));

        $organisationRepo = $manager->getRepository('RuchJowUserBundle:Organisation');
        $organisations = $organisationRepo->findAll();

        // Admin
        $this->makeUser(
            'admin',
            'admin@ruchjow.pl',
            'Admin',
            'Almighty',
            'ROLE_ADMIN',
            'admin',
            $country,
            null,
            null,
            false
        );

        // Test
        for ($i = 1; $i <= 5; $i++) {
            $username = 'user' . $i;
            $email = $username . '@ruchjow.pl';

            $organisation = rand(0, 1) == 0 ? null : $organisations[array_rand($organisations)];

            $this->makeUser(
                $username,
                $email,
                'Firstname' . $i,
                'Lastname' . $i,
                'ROLE_USER',
                $username,
                $country,
                $communes[array_rand($communes)],
                $organisation,
                true
            );
        }


        $faker = FakerFactory::create('pl_PL');

        for ($i = 1; $i <= 50; $i++)
        {
            if ($i % 10 === 0) {
                echo $i ."\n";
            }
            $organisation = rand(0, 1) == 0 ? null : $organisations[array_rand($organisations)];

            $this->makeUser(
                $faker->unique()->userName,
                $faker->unique()->email,
                $faker->firstName,
                $faker->lastName,
                'ROLE_USER',
                null,
                $country,
                $communes[array_rand($communes)],
                $organisation

            );

            $manager->flush();
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