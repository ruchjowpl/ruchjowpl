<?php

namespace RuchJow\UserBundle\Controller;

use RuchJow\AddressBundle\Entity\Address;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\TerritorialUnitsBundle\Entity\CommuneRepository;
use RuchJow\UserBundle\Entity\Organisation;
use RuchJow\UserBundle\Entity\OrganisationRepository;
use RuchJow\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\UserBundle\Controller
 *
 * @Route("/")
 */
class UserController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/ajax/support", name="user_ajax_support", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function registerUserAction()
    {

        // Get POSTed Json and validate its format.
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'nick'             => array('type' => 'string'),
                    'email'            => array('type' => 'string'),
//                    'phone'            => array('type' => 'string', 'optional' => true),
                    'commune'          => array('type' => 'entityId', 'entity' => 'RuchJowTerritorialUnitsBundle:Commune', 'optional' => true),
                    'organisationUrl'  => array('type' => 'string', 'optional' => true),
                    'organisationName' => array('type' => 'string', 'optional' => true),
                    'password'         => array('type' => 'string', 'optional' => true),
                    'referral'         => array('type' => 'string', 'optional' => true),
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $userValidation = $this->getParameter('ruch_jow_user.user_validation');

        $err = $this->validate($userValidation, $data);
        if ($err) {
            return $this->createJsonErrorResponse($err);
        }

        // Check if nick is unique.
        $userManager = $this->getUserManager();
        if ($userManager->checkFieldValueExists('nick', $data['nick'])) {
            return $this->createJsonErrorResponse('User with this nick has expressed their support already.');
        }

        // Check if email is unique.
        if ($userManager->checkFieldValueExists('email', $data['email'])) {
            return $this->createJsonErrorResponse('User with this email has expressed their support already.');
        }

        // Check if commune or organisation is set.
        if (!isset($data['commune']) && !isset($data['organisationUrl'])) {
            return $this->createJsonErrorResponse('Commune can only be omitted if organisation has been chosen.');
        }

        $em = $this->getDoctrine()->getManager();

        // Create user.
        $user = $userManager->createUser();

        $user
            ->setNick($data['nick'])
            ->setEmail($data['email']);

//        if (isset($data['phone']) && $data['phone']) {
//            $user->setPhone($data['phone']);
//        }

        // Attach commune (if applicable).
        if (isset($data['commune'])) {
            /** @var CommuneRepository $communeRepo */
            $communeRepo = $this->getRepository('RuchJowTerritorialUnitsBundle:Commune');
            /** @var Commune $commune */
            $commune = $communeRepo->find($data['commune']);

            $user->setCommune($commune);
        }

        // Attach organisation (if applicable)
        if (isset($data['organisationUrl'])) {
            /** @var OrganisationRepository $organisationRepo */
            $organisationRepo = $this->getRepository('RuchJowUserBundle:Organisation');
            $organisation     = $organisationRepo->findOneByUrl($data['organisationUrl']);

            if (!$organisation) {
                if (!isset($data['organisationName'])) {
                    return $this->createJsonErrorResponse('Name must be provided for new organisation.');
                }

                $organisation = new Organisation();
                $organisation
                    ->setUrl($data['organisationUrl'])
                    ->setName($data['organisationName']);

                $em->persist($organisation);
            }

            $user->setOrganisation($organisation);
        }


        // Set password (if applicable).
        if (isset($data['password'])) {
            $user->setPlainPassword($data['password']);
        } else {
            // Set impossible password hash.
            $user->setPassword('');
        }


        // Handle referral token
        if (isset($data['referral'])) {
            $referrer = $userManager->findUserByReferralToken($data['referral']);

            if ($referrer) {
                $user->setReferrer($referrer);
            }
        }


        // Prepare registration token and sent confirmation email.
        $user
            ->setEnabled(false)
            ->setConfirmationToken($this->getTokenGenerator()->generateToken());

        $userManager->updateUser($user); // It also flushes doctrine entity manager.

        // TODO move this to events.
        $this->get('ruch_jow_user.mailer')->sendConfirmationEmailMessage($user);

        return $this->createJsonResponse('ok');
    }

    /**
     * @return Response
     *
     * @Route("/ajax/support/confirm", name="user_ajax_support_confirm", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function confirmUserAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type' => 'string'
            ),
            $token
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $userManager = $this->getUserManager();

        /** @var User $user */
        $user = $userManager->findUserByConfirmationToken($token);

        if (!$user) {
            return $this->createJsonResponse(array('status' => 'token_not_exists'));
        }


        $userManager->confirmRegistration($user);


//        $this->getPointsManager()->addPoints($user, 'user.support', null, null, false);
//
//        if ($user->getReferrer()) {
//            $additionalData = array(
//                'user' => $user->getId()
//            );
//            $this->getPointsManager()->addPoints($user->getReferrer(), 'user.referral', null, $additionalData, false);
//        }

        $userManager->updateUser($user);

        return $this->createJsonResponse(array('status' => 'success'));
    }


    /**
     * @return Response
     *
     * @Route("/ajax/password/forgot", name="user_ajax_create_reset_password_link", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function forgotPasswordAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'email' => array('type' => 'string',),
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $userValidation = $this->getParameter('ruch_jow_user.user_validation');
        if (isset($userValidation['email']) && isset($userValidation['email']['pattern'])) {
            if (!preg_match($userValidation['email']['pattern'], $data['email'])) {
                return $this->createJsonErrorResponse('Email format is incorrect');
            }
        }

        $userManager = $this->getUserManager();
        $user        = $userManager->findUserByEmail($data['email']);

        if (!$user) {
            return $this->createJsonResponse(array('status' => 'user_not_found'));
        }

        $userManager->generatePasswordResetToken($user);
        $userManager->updateUser($user);

        $this->get('ruch_jow_user.mailer')->sendPasswordResetEmailMessage($user);

        return $this->createJsonResponse(array('status' => 'success'));
    }

    /**
     * @return Response
     *
     * @Route("/ajax/password/check_reset_token", name="user_ajax_check_reset_password_token", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function checkResetTokenAction()
    {
        $error = $this->validateRequestJson(
            array('type' => 'string',),
            $token
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $user = $this->getUserManager()->findUserByPasswordResetToken($token);
        if (!$user) {
            return $this->createJsonResponse(array('status' => 'incorrect_token'));
        }

        $ttl = $this->getParameter('ruch_jow_user.password_reset.token_expiration_time');
        if (!$user->isPasswordResetRequestNonExpired($ttl)) {
            return $this->createJsonResponse(array('status' => 'expired_token'));
        }

        return $this->createJsonResponse(array('status' => 'success'));
    }


    /**
     * @return Response
     *
     * @Route("/ajax/password/set_new", name="user_ajax_set_new_password", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function setNewPasswordAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'token'    => array('type' => 'string',),
                    'password' => array('type' => 'string',),
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $userValidation = $this->getParameter('ruch_jow_user.user_validation');
        if (isset($userValidation['password']) && isset($userValidation['password']['pattern'])) {
            if (!preg_match($userValidation['password']['pattern'], $data['password'])) {
                return $this->createJsonErrorResponse('Password format is incorrect');
            }
        }

        $userManager = $this->getUserManager();
        $user        = $userManager->findUserByPasswordResetToken($data['token']);

        if (!$user) {
            return $this->createJsonResponse(array('status' => 'incorrect_token'));
        }

        $user->setPlainPassword($data['password']);
        $userManager->removePasswordResetToken($user);
        $userManager->updateUser($user);

        return $this->createJsonResponse(array('status' => 'success'));
    }

    /**
     * @return Response
     *
     * @Route("/ajax/remove/account", name="user_ajax_remove_account", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function removeAccountAction()
    {
        $userManager = $this->getUserManager();
        $user        = $this->getUser();

        if (!$user) {
            return $this->createJsonResponse(array('status' => 'user_not_found'));
        }

        $userManager->generateRemoveAccountToken($user);
        $userManager->updateUser($user);

        $this->get('ruch_jow_user.mailer')->sendRemoveAccountEmailMessage($user);

        return $this->createJsonResponse(array('status' => 'success'));
    }


    /**
     * @return Response
     *
     * @Route("/ajax/remove/account/confirm", name="user_ajax_remove_account_confirm", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function confirmRemoveAccountAction()
    {
        $error = $this->validateRequestJson(
            array('type' => 'string',),
            $token
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $user = $this->getUserManager()->findUserByRemoveAccountToken($token);
        if (!$user) {
            return $this->createJsonResponse(array('status' => 'incorrect_token'));
        }

        $ttl = $this->getParameter('ruch_jow_user.remove_account.token_expiration_time');
        if (!$user->isRemoveAccountRequestNonExpired($ttl)) {
            return $this->createJsonResponse(array('status' => 'expired_token'));
        }

        $this->getUserManager()->removeAccount($user);

        $this->get('security.token_storage')->setToken(null);
        $this->get('request')->getSession()->invalidate();

        return $this->createJsonResponse(array('status' => 'success'));
    }

    /**
     * @return Response
     *
     * @Route("/ajax/update", name="user_ajax_update", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function updateUserDataAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'organisation' => array(
                        'type'     => 'array',
                        'optional' => true,
                        'children' => array(
                            'url'  => array('type' => 'string', 'optional' => true),
                            'name' => array('type' => 'string', 'optional' => true),
                        )
                    ),
                    'phone'        => array(
                        'type'     => 'array',
                        'optional' => true,
                        'children' => array(
                            'phone' => array('type' => 'string', 'optional' => true),
                        ),
                    ),
                    'address'      => array(
                        'type'     => 'array',
                        'optional' => true,
                        'children' => array(
                            'firstName' => array('type' => 'string', 'optional' => true),
                            'lastName'  => array('type' => 'string', 'optional' => true),
                            'street'    => array('type' => 'string', 'optional' => true),
                            'house'     => array('type' => 'string', 'optional' => true),
                            'flat'      => array('type' => 'string', 'optional' => true),
                            'postCode'  => array('type' => 'string', 'optional' => true),
                            'city'      => array('type' => 'string', 'optional' => true),
                        )
                    ),
                    'social_links' => array(
                        'type'     => 'array',
                        'optional' => true,
                        'children' => array(
                            '#default' => array(
                                'type' => 'string',
                            ),
                        )
                    ),
                    'about'        => array(
                        'type'     => 'string',
                        'optional' => true,
                    ),
                    'password'     => array(
                        'type'     => 'array',
                        'optional' => true,
                        'children' => array(
                            'newPassword'     => array('type' => 'string', 'optional' => true),
                            'currentPassword' => array('type' => 'string', 'optional' => true)
                        ),
                    ),
                    'visibility' => array(
                        'type' => 'array',
                        'optional' => true,
                        'children' => array(
                            'firstName' => array('type' => 'boolean', 'optional' => true),
                            'lastName' => array('type' => 'boolean', 'optional' => true),
                            'organisation' => array('type' => 'boolean', 'optional' => true),
                            'socialLinks' => array('type' => 'boolean', 'optional' => true),
                            'about' => array('type' => 'boolean', 'optional' => true),
                        )
                    ),
                    'displayNameFormat' => array(
                        'type' => 'string',
                        'optional' => true,
                        'in' => array(User::DISPLAY_NAME_NICK, User::DISPLAY_NAME_FULL_NAME),
                    ),
                    'commune' => array(
                        'type' => 'entityId',
                        'entity' => 'RuchJowTerritorialUnitsBundle:Commune',
                        'optional' => true
                    ),
                ),
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        /** @var User $user */
        $user = $this->getUser();
        // This should never happen.
        if (!$user) {
            return $this->createJsonErrorResponse('User not found');
        }

        $em             = $this->getDoctrine()->getManager();
        $userValidation = $this->getParameter('ruch_jow_user.user_validation');

        // Organisation
        if (isset($data['organisation'])) {

            $organisationData = $data['organisation'];

            if (isset($organisationData['url'])) {


                // Validate url pattern.
                if (
                    isset($userValidation['organisation_url'])
                    && isset($validation['organisation_url']['pattern'])
                    && !preg_match($validation['organisation_url']['pattern'], $organisationData['url'])
                ) {
                    return $this->createJsonErrorResponse(
                        isset($validation['patternError']) ?
                            $validation['patternError'] :
                            'Organisation url format is incorrect.'
                    );
                }


                // Attach organisation (if applicable)

                /** @var OrganisationRepository $organisationRepo */
                $organisationRepo = $this->getRepository('RuchJowUserBundle:Organisation');
                $organisation     = $organisationRepo->findOneByUrl($organisationData['url']);

                if (!$organisation) {
                    if (!isset($organisationData['name'])) {
                        return $this->createJsonErrorResponse('Name must be provided for new organisation.');
                    }

                    // Validate name pattern.
                    if (
                        isset($userValidation['organisation_name'])
                        && isset($validation['organisation_name']['pattern'])
                        && !preg_match($validation['organisation_name']['pattern'], $organisationData['url'])
                    ) {
                        return $this->createJsonErrorResponse(
                            isset($validation['patternError']) ?
                                $validation['patternError'] :
                                'Organisation name format is incorrect.'
                        );
                    }

                    $organisation = new Organisation();
                    $organisation
                        ->setUrl($organisationData['url'])
                        ->setName($organisationData['name']);

                    $em->persist($organisation);
                }

                $user->setOrganisation($organisation);
            } else {
                // Organisation will be detached from user.
                $user->setOrganisation(null);
            }
            $em->persist($user);
        }

        // Organisation
        if (isset($data['commune'])) {
            $commune = $this->getRepository('RuchJowTerritorialUnitsBundle:Commune')->find($data['commune']);

            if (!$commune) {
                // This should never happen (as we already verified commune id).
                return $this->createJsonErrorResponse('Commune not found.');
            }

            $user->setCommune($commune);
            $em->persist($user);
        }

        // Phone No.
        if (isset($data['phone'])) {

            $phoneData = $data['phone'];

            if (isset($phoneData['phone'])) {
                // Validate name pattern.
                if (
                    isset($userValidation['phone'])
                    && isset($validation['phone']['pattern'])
                    && !preg_match($validation['phone']['pattern'], $phoneData['phone'])
                ) {
                    return $this->createJsonErrorResponse(
                        isset($validation['patternError']) ?
                            $validation['patternError'] :
                            'Phone number format is incorrect.'
                    );
                }

                $user->setPhone($phoneData['phone']);
            } else {
                $user->setPhone(null);
            }
            $em->persist($user);
        }

        // Address
        if (isset($data['address'])) {

            $addressData = $data['address'];

            if (!empty($addressData)) {

                $validation = $this->getParameter('ruch_jow_address.validation');

                $err = $this->validate($validation, $addressData);
                if ($err) {
                    return $this->createJsonErrorResponse($err);
                }

                $address = $user->getAddress();
                if (!$address) {
                    $address = new Address();
                    $user->setAddress($address);
                }

                $address
                    ->setFirstName(isset($addressData['firstName']) ? $addressData['firstName'] : null)
                    ->setLastName(isset($addressData['lastName']) ? $addressData['lastName'] : null)
                    ->setStreet(isset($addressData['street']) ? $addressData['street'] : null)
                    ->setHouse(isset($addressData['house']) ? $addressData['house'] : null)
                    ->setFlat(isset($addressData['flat']) ? $addressData['flat'] : null)
                    ->setPostCode(isset($addressData['postCode']) ? $addressData['postCode'] : null)
                    ->setCity(isset($addressData['city']) ? $addressData['city'] : null);

                $em->persist($address);
            } else {
                $user->setAddress(null);
            }
            $em->persist($user);
        }

        // Social links
        if (isset($data['social_links'])) {

            $socialLinksData = $data['social_links'];

            if (empty($socialLinksData)) {
                return $this->createJsonErrorResponse('At least one path must by provided under social link update group.');
            }

            try {
                $errMsg = $this->getUserManager()->setSocialLinks($user, $socialLinksData);

                if ($errMsg) {
                    return $this->createJsonErrorResponse($errMsg);
                }
            } catch (\InvalidArgumentException $e) {
                return $this->createJsonErrorResponse('Invalid parameters received.');
            }
        }

        // About
        if (isset($data['about'])) {

            $aboutData = $data['about'];

            if ($aboutData) {
                // Validate name pattern.
                if (
                    isset($userValidation['about'])
                    && isset($validation['about']['pattern'])
                    && !preg_match($validation['about']['pattern'], $aboutData)
                ) {
                    return $this->createJsonErrorResponse(
                        isset($validation['patternError']) ?
                            $validation['patternError'] :
                            'About format is incorrect.'
                    );
                }

                $user->setAbout($aboutData);
            } else {
                $user->setAbout('');
            }
            $em->persist($user);
        }

        // Password
        if (isset($data['password'])) {

            $userManager  = $this->getUserManager();
            $passwordData = $data['password'];
            /** @var User $user */
            $user = $this->getUser();

            $encoder = $this->get('security.encoder_factory')->getEncoder($user);

            if (!empty($passwordData)) {
                if ($encoder->isPasswordValid($user->getPassword(), $passwordData['currentPassword'], $user->getSalt())) {

                    $userValidation = $this->getParameter('ruch_jow_user.user_validation');

                    if (
                        isset($userValidation['password'])
                        && isset($userValidation['password']['pattern'])
                        && !preg_match($userValidation['password']['pattern'], $passwordData['newPassword'])
                    ) {
                        return $this->createJsonErrorResponse('Password format is incorrect');
                    }

                    $user->setPlainPassword($passwordData['newPassword']);
                    $userManager->updateUser($user);
                } else {
                    return $this->createJsonErrorResponse('New password is not valid');
                }
            }
        }

        // Visibility
        if (isset($data['visibility'])) {
            $visibility = $data['visibility'];

            // First name
            if (isset($visibility['firstName'])) {
                $user->setFirstNameVisible($visibility['firstName']);
            }

            // Last name
            if (isset($visibility['lastName'])) {
                $user->setLastNameVisible($visibility['lastName']);
            }

            // Organisation
            if (isset($visibility['organisation'])) {
                $user->setOrganisationVisible($visibility['organisation']);
            }

            // Social links
            if (isset($visibility['socialLinks'])) {
                $user->setSocialLinksVisible($visibility['socialLinks']);
            }

            // About
            if (isset($visibility['about'])) {
                $user->setAboutVisible($visibility['about']);
            }
        }

        // Display name format
        if (isset($data['displayNameFormat'])) {
            $user->setDisplayNameFormat($data['displayNameFormat']);
        }

        $em->flush();

        return $this->createJsonResponse(array('status' => 'success'));
    }


    /**
     * @return Response
     *
     * @Route("/ajax/pre_signed_data", name="user_ajax_get_pre_signed_data", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function getPreSignedDataAction()
    {
        $error = $this->validateRequestJson(
            array('type' => 'string',),
            $token
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $um   = $this->getUserManager();
        $data = $um->getPreSignedUserData($token);

        if (!$data) {
            return $this->createJsonResponse(array('status' => 'token_not_exists'));
        }

        $ret = array(
            'status' => 'success',
            'nick'   => $data->getNick(),
            'email'  => $data->getEmail(),
        );

        if ($um->findUserByEmail($data->getEmail())) {
            $ret['status'] = 'email_taken';
            $ret['email']  = null;
        }

        return $this->createJsonResponse($ret);
    }


    protected function validate($definition, $data)
    {
        foreach ($definition as $key => $validation) {

            // If field is not present...
            if (!isset($data[$key])) {
                // ... show error if field is required...
                if (
                    isset($validation['required'])
                    && $validation['required']
                ) {
                    return isset($validation['requiredError']) ?
                        $validation['requiredError'] :
                        $key . ' is required.';
                }

                // ... else continue to next field.
                continue;
            }

            // Check pattern.
            if (
                isset($validation['pattern'])
                && !preg_match($validation['pattern'], $data[$key])
            ) {
                return isset($validation['patternError']) ?
                    $validation['patternError'] :
                    $key . ' format is incorrect.';
            }
        }

        return null;
    }


    /**
     * @return Response
     *
     * @Route("/ajax/invite_friends", name="user_ajax_invite_friends", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function inviteFriendsAction()
    {

        $emails = array();

        // Get POSTed Json and validate its format.
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'minCount' => 1,
                'maxCount' => 10,
                'children' => array(
                    '#default' => array(
                        'type'    => 'string',
                        'pattern' => '/^[a-zA-Z0-9.!#$%&\'*+\\/?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/',
                    ),
                )
            ),
            $emails
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->createJsonErrorResponse('User not found.');
        }

        $userManager = $this->getUserManager();
//        $userManager->updateUser($user); // It also flushes doctrine entity manager.
        $existsEmails = $userManager->getRepository()->findEmails($emails);

        $emails = \array_diff($emails, $existsEmails);

        // TODO move this to events.
        $this->get('ruch_jow_user.mailer')->sendInvitationEmails($user, $emails);

        return $this->createJsonResponse(array(
            'status'       => 'ok',
            'existsEmails' => $existsEmails
        ));
    }

    /**
     * @return Response
     *
     * @Route("/ajax/invite_friends/check", name="user_ajax_invite_friends_check", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function inviteFriendsCheckAction()
    {
        $email = '';

        $error = $this->validateRequestJson(
            array('type' => 'string',),
            $email
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $user = $this->getUserManager()->findUserByEmail($email);
        if ($user) {
            return $this->createJsonResponse(array('status' => 'exist_email'));
        }

        return $this->createJsonResponse(array('status' => 'success'));
    }
}
