<?php

namespace RuchJow\UserBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\AbstractQuery;
use FOS\UserBundle\Doctrine\UserManager as FOSDoctrineUserManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use RuchJow\SocialLinksBundle\Entity\SocialLinkManager;
use RuchJow\TerritorialUnitsBundle\Entity\Country;
use RuchJow\UserBundle\Services\TokenGenerator;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \DateTime;
use FOS\UserBundle\Model\UserInterface;

class UserManager extends FOSDoctrineUserManager
{

    /**
     * @var TokenGenerator
     */
    protected $tokenGenerator;

    protected $router;

    protected $socialLinkManager;

    protected $preSignedUserDataRepository;

    protected $container;

    /**
     * {@inheritdoc}
     *
     *
     */
    public function __construct(Router $router, TokenGenerator $tokenGenerator, SocialLinkManager $socialLinkManager, EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class, ContainerInterface $container)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $om, $class);

        $this->router         = $router;
        $this->tokenGenerator = $tokenGenerator;
        $this->socialLinkManager = $socialLinkManager;
        $this->container = $container;
    }

    /**
     * @return UserRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }


    /**
     * @return PreSignedUserDataRepository
     */
    public function getPreSignedUserDataRepository()
    {
        if (!$this->preSignedUserDataRepository) {
            $this->preSignedUserDataRepository =
                $this->objectManager->getRepository('RuchJowUserBundle:PreSignedUserData');
        }

        return $this->preSignedUserDataRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser()
    {
        /** @var User $user */
        $user = parent::createUser();
        $reminder  = $this->container->getParameter('ruch_jow_user.reminder.email.remainders');

        $nextReminder=(new \DateTime())->modify('+'.$reminder[0] .'minutes');
        $token = $this->tokenGenerator->generateToken('referral.link.token');
        $user->setReferralToken($token);
        $user->setNextReminderAt($nextReminder);
        $user->setCreatedAt(new \DateTime());

        return $user;
    }



    /**
     * @param User $user
     */
    public function confirmRegistration($user)
    {
        $user
            ->setEnabled(true)
            ->setSupports(true)
            ->setConfirmationToken(null)
            ->setNextReminderAt(null)
            ->addRole('ROLE_REGISTERED_USER');
    }

    public function checkFieldValueExists($field, $value)
    {
        $map = array(
            'nick'  => array(
                'u.usernameCanonical',
                $this->usernameCanonicalizer->canonicalize($value)
            ),
            'email' => array(
                'u.emailCanonical',
                $this->emailCanonicalizer->canonicalize($value)
            )
        );

        if (!isset($map[$field])) {
            throw new \InvalidArgumentException('Field ' . $field . ' is not supported.');
        }


        /** @var UserRepository $repo */
        $repo = $this->repository;
        $qb = $repo->createQueryBuilder('u');

        $qb->select('count(u.id) cnt')
            ->where($qb->expr()->eq($map[$field][0], '?1'))
            ->setParameter(1, $map[$field][1]);

        $ret = $qb
            ->getQuery()
            ->getSingleResult();

        return $ret['cnt'] !== '0';
    }

    /**
     * @param User $user
     */
    public function generatePasswordResetToken(User $user)
    {
        $token = $this->tokenGenerator->generateToken('password.reset.token');
        $user->setPasswordResetToken($token);
        $user->setPasswordResetRequestedAt(new \DateTime());

        $this->objectManager->persist($user);
    }

    /**
     * @param User $user
     */
    public function removePasswordResetToken(User $user)
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordResetRequestedAt(null);

        $this->objectManager->persist($user);
    }


    /**
     * @param User $user
     */
    public function generateRemoveAccountToken(User $user)
    {
        $token = $this->tokenGenerator->generateToken('remove.account.token');
        $user->setRemoveAccountToken($token);
        $user->setRemoveAccountRequestedAt(new \DateTime());

        $this->objectManager->persist($user);
    }


    /**
     * @param User $user
     */
    public function removeRemoveAccountToken(User $user)
    {
        $user->setRemoveAccountToken(null);
        $user->setRemoveAccountRequestedAt(null);

        $this->objectManager->persist($user);
    }


    public function removeAccount(User $user, $flush = true)
    {
        if ($address = $user->getAddress()) {
            $this->objectManager->remove($address);
        }

        if ($socialLinks = $user->getSocialLinks()) {
            foreach ($socialLinks as $socialLink) {
                $user->removeSocialLink($socialLink);
                $this->objectManager->remove($socialLink);
            }
        }

        $user
            ->setAbout('')
            ->setAddress(null)
            ->setAboutVisible(false)
            ->setCommune(null)
            ->setConfirmationToken(null)
            ->setDisplayNameFormat(User::DISPLAY_NAME_REMOVED)
            ->setRemoveAccountToken(null)
            ->setEmail(md5($user->getEmailCanonical()))
            ->setEmailCanonical(md5($user->getEmailCanonical()))
            ->setFirstName('')
            ->setFirstNameVisible(false)
            ->setLastName('')
            ->setLastNameVisible(false)
            ->setRemoveAccountToken(null)
            ->setCommune(null)
            ->setOrganisation(null)
            ->setOrganisationVisible(false)
            ->setLocalGov(false)
            ->setReferralToken(null)
            ->setPasswordResetToken(null)
            ->setPhone(null)
            ->setReferrer(null)
            ->setSocialLinksVisible(false)
            ->setSupports(false)
            ->setLocked(true)
            ->setEnabled(false)
            ->setRoles(array());

        $this->objectManager->persist($user);

        if ($flush) {
            $this->objectManager->flush();
        }
    }

    /**
     *
     * @param User     $user
     * @param string[] $linksData
     * @param bool     $flush
     *
     * @throws \InvalidArgumentException
     *
     * @return null
     */
    public function setSocialLinks($user, $linksData, $flush = false)
    {

        $em = $this->objectManager;

        $userLinks = $user->getSocialLinks();

        foreach ($userLinks as $userLink) {
            $service = $userLink->getService();
            if (array_key_exists($service, $linksData)) {
                if (!$linksData[$service]) {
                    $em->remove($userLink);
                } else {
                    try {
                        $this->socialLinkManager->updateSocialLink(
                            $userLink,
                            $service,
                            $linksData[$service]
                        );
                    } catch (\InvalidArgumentException $e) {
                        throw $e;
                    }
                    $em->persist($userLink);
                }

                unset($linksData[$service]);
            }
        }

        foreach ($linksData as $service => $path) {
            try {
                $socialLink = $this->socialLinkManager->createSocialLink($service, $path, $err);
            } catch (\InvalidArgumentException $e) {
                throw $e;
            }

            if (!$socialLink) {
                return $err;
            }

            $socialLink->setUser($user);
            $em->persist($socialLink);
        }
        $em->persist($user);

        if ($flush) {
            $em->flush();
        }

        return null;
    }

    /**
     * @param User $user
     *
     * @return null|string
     */
    public function getReferralLink($user)
    {
        if (!$user->getReferralToken()) {
            return null;
        }

        return $this->router->generate('frontend_homepage', array(), true) . '?url=' . urlencode('/action/referral:' . $user->getReferralToken());
    }

    /**
     * @param User $user
     *
     * @return null|string
     */
    public function getConfirmationLink($user)
    {
        if (!$user->getConfirmationToken()) {
            return null;
        }

        return $this->router->generate('frontend_homepage', array(), true) . '?url=' . urlencode('/action/confirm_support:' . $user->getConfirmationToken());
    }

    /**
     * Finds a user by referral token.
     *
     * @param string $token
     *
     * @return User
     */
    public function findUserByReferralToken($token)
    {
        return $this->findUserBy(array('referralToken' => $token));
    }

    /**
     * Finds a user by password reset token.
     *
     * @param string $token
     *
     * @return User
     */
    public function findUserByPasswordResetToken($token)
    {
        return $this->findUserBy(array('passwordResetToken' => $token));
    }

    /**
     * Finds a user by password reset token.
     *
     * @param string $token
     *
     * @return User
     */
    public function findUserByRemoveAccountToken($token)
    {
        return $this->findUserBy(array('removeAccountToken' => $token));
    }

    /**
     * Finds a user by their name and email
     *
     * @param $nick
     * @param $email
     *
     * @return User
     */
    public function findUserByNickAndEmail($nick, $email)
    {
        $nick  = $this->usernameCanonicalizer->canonicalize($nick);
        $email = $this->emailCanonicalizer->canonicalize($email);

        return $this->findUserBy(array('usernameCanonical' => $nick, 'emailCanonical' => $email));
    }

    /**
     * Finds a user by their name and email
     *
     * @param $email
     *
     * @return User
     */
    public function findUserByEmail($email)
    {
        $email = $this->emailCanonicalizer->canonicalize($email);

        return $this->findUserBy(array('emailCanonical' => $email));
    }

    /**
     * @param string|integer $dateIntervalStr
     * @param bool           $localGovOnly
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return integer
     */
    public function getActiveUsersCount($dateIntervalStr = null, $localGovOnly = false) {

        /** @var UserRepository $repo */
        $repo = $this->repository;
        $qb = $repo->createQueryBuilder('u');
        $qb->select('count(distinct u.id)')
            ->where($qb->expr()->eq('u.enabled', 1));

        if ($dateIntervalStr) {
            if (is_integer($dateIntervalStr)) {
                $dateIntervalStr = 'P' . $dateIntervalStr . 'D';
            }

            $interval = new \DateInterval($dateIntervalStr);
            $date = new \DateTime();
            $date->setTime(0, 0, 0)->sub($interval);

            $qb
                ->andWhere($qb->expr()->gte('u.supportedAt', ':supportMinDate'))
                ->setParameter('supportMinDate', $date);
        }

        if ($localGovOnly) {
            $qb->andWhere($qb->expr()->eq('u.localGov', 1));
        }

        $ret = $qb->getQuery()->getSingleResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return (int) $ret;
    }

    /**
     * @return integer
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLocalGovUsersCount() {
        /** @var UserRepository $repo */
        $repo = $this->repository;
        $qb = $repo->createQueryBuilder('u');
        $qb->select('count(distinct u.id)')
            ->where($qb->expr()->eq('u.enabled', 1));

        $ret = $qb->getQuery()->getSingleResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $ret;
    }

    /**
     * @param User $user
     */
    public function updateUserFields(User $user)
    {
        if ($user->isSupports() && !$user->getSupportedAt()) {
            $user->setSupportedAt(new \DateTime());
        }
    }

    /**
     * @param string $token
     *
     * @return PreSignedUserData
     */
    public function getPreSignedUserData($token)
    {
        $repo = $this->getPreSignedUserDataRepository();

        /** @var PreSignedUserData $data */
        return $repo->find($token);
    }

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        //TODO: delete this plz
        if (!empty($user->getFacebookId()) && is_null($user->getCountry())) {
            $mainCountry = $this->objectManager->getRepository('RuchJowTerritorialUnitsBundle:Country')->findMainCountry();
            $user->setCountry($mainCountry);
        }

        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }
}