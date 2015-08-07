<?php

namespace RuchJow\PageFoundationBundle\Controller;

use RuchJow\SocialLinksBundle\Entity\SocialLink;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class DataController
 *
 * @package RuchJow\PageFoundationBundle\Controller
 *
 * @Route("/foundation/cif")
 */
class DataController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/auth_forms_data", name="page_foundation_cif_auth_form_data", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getMainDataAction()
    {
        $data = array();

        $data['login_form'] = array(
            'csrf_token' => $this->container->has('form.csrf_provider')
                ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
                : null,
            'url'        => $this->generateUrl('fos_user_security_check', array(), false),
        );
        $data['logout_url'] =
            $this->generateUrl('fos_user_security_logout', array(), false);

        return $this->createJsonResponse($data);
    }

    /**
     * @return Response
     *
     * @Route("/user_roles", name="page_foundation_cif_user_roles", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getUserRolesAction()
    {
        $exposedRoles = $this->container
            ->getParameter('ruch_jow_page_foundation.exposed_user_roles');
        $userRoles    = array();

        foreach ($exposedRoles as $role) {
            if ($this->isGranted($role)) {
                $userRoles[$role] = true;
            }
        }

        /** @var $user User */
        $user = $this->getUser();

        if (!$user) {
            return $this->createJsonResponse(null);
        }

        $userArray = array(
            'username'          => $user->getUsername(),
            'displayName'       => $user->getDisplayName(),
            'user_id'           => $user->getId(),
            'roles'             => $userRoles,
            'email'             => $user->getEmail(),
            'phone'             => $user->getPhone(),
            'displayNameFormat' => $user->getDisplayNameFormat(),
            'visibility'        => $user->getVisibilityArray(),
            'about'             => $user->getAbout(),
        );

        if ($address = $user->getAddress()) {
            $userArray['address'] = $address->toArray();
        }

        if ($commune = $user->getCommune()) {
            $userArray['commune'] = $commune->toArray();
        }

        if ($organisation = $user->getOrganisation()) {
            $userArray['organisation'] = $organisation->toArray();
        }

        $referralUrl = $this->getUserManager()->getReferralLink($user);
        if ($referralUrl) {
            $userArray['referralUrl'] = $referralUrl;
        }

//        $socialLinks      = $user->getSocialLinks();
//        $services         = $this->getParameter('ruch_jow_social_links.services');
//        $socialLinksArray = array();
//
//        foreach ($socialLinks as $socialLink) {
//            if (isset($services[$socialLink->getService()])) {
//                $socialLinksArray[$socialLink->getService()] = $socialLink->getPath();
//            }
//        }
//        $userArray['socialLinks'] = $socialLinksArray;

        $userArray['socialLinks'] = $this->socialLinksToArray($user->getSocialLinks());
        $userArray['socialLinksFull'] = $this->socialLinksToArray($user->getSocialLinks(), true);


        return $this->createJsonResponse($userArray);
    }

    /**
     * @param string $username
     *
     * @return Response
     *
     * @Route("/user_public_data/{username}", name="page_foundation_cif_user_public_data", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getUserPublicDataAction($username)
    {
        /** @var User $user */
        $user = $this->getUserManager()->findUserByUsername($username);

        if (!$user || !$user->isEnabled() || !$user->isSupports()) {
            return $this->createJsonErrorResponse(array(
                'status'  => 'fail',
                'message' => 'User not found.'
            ), 404);
        }

        $userArray = array(
            'nick'        => $user->getUserName(),
            'displayName' => $user->getDisplayName(),
        );

        // COUNTRY
        if ($country = $user->getCountry()) {
            $userArray['country'] = $country->toArray();
        }

        // COMMUNE
        if ($commune = $user->getCommune()) {
            $userArray['commune'] = $commune->toArray();
        }

        // FIRST NAME
        if ($user->getFirstNameVisible()) {
            $userArray['firstName'] = $user->getFirstName();
        }

        // LAST NAME
        if ($user->getLastNameVisible()) {
            $userArray['lastName'] = $user->getLastName();
        }

        // ORGANISATION
        if ($user->getOrganisationVisible()) {
            if ($organisation = $user->getOrganisation()) {
                $userArray['organisation'] = $organisation->toArray();
                $userArray['organisation']['fullUrl'] = $organisation->getUrl(true);
            } else {
                $userArray['organisation'] = null;
            }
        }

        // SOCIAL LINKS
        if ($user->getSocialLinksVisible()) {
            $userArray['socialLinks']     = $this->socialLinksToArray($user->getSocialLinks());
            $userArray['socialLinksFull'] = $this->socialLinksToArray($user->getSocialLinks(), true);
        }

        // STATISTICS
        $userArray['stats'] = $this->getStatisticManager()->getUserStats($user, $user->getOrganisationVisible(), true);

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data'   => $userArray,
        ));
    }

    /**
     * @return Response
     *
     * @Route("/user_points_history", name="page_foundation_cif_user_points_history", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getUserPointsHistoryAction()
    {
        /** @var $user User */
        $user = $this->getUser();

        if (!$user) {
            return $this->createJsonResponse(null);
        }

        $pm = $this->getPointsManager();

        $points = $pm->getPointsByUser($user->getId());

        $ret = array();
        foreach ($points as $entry) {
            $details = '';
            if ($entry->getType() === 'user.referral') {
                $data = unserialize($entry->getDataSerial());
                if (isset($data['user']) && !empty($data['user'])) {
                    $user = $this->getUserManager()->findUserBy(array('id'=> $data['user']));
                    if (!$user || !$user->isSupports()) {
                        $details = '%unknown%';
                    } else {
                        $details = $user->getDisplayName();
                    }
                }
            }
            $ret[] = array(
                'date'    => $entry->getDate()->format('D M d Y H:i:s O'),
                'points'  => $entry->getPoints(),
                'type'    => $entry->getType(),
                'details' => $details
            );
        }

        return $this->createJsonResponse($ret);
    }


    /**
     * @param $field
     *
     * @return Response
     *
     * @Route("/user_fvu/{field}", name="page_foundation_cif_user_field_value_unique", requirements={"field"="nick|email"}, options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Method({"POST"})
     */
    public function checkUserFieldValueUniqueAction($field)
    {

        $this->validateRequestJson(
            array('type' => 'string',),
            $value
        );

        if (!$value) {
            return $this->createJsonResponse(true);
        }

        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        return $this->createJsonResponse(!$userManager->checkFieldValueExists($field, $value));
    }

    /**
     * @return Response
     *
     * @Route("/points_definitions", name="page_foundation_cif_points_definitions", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getPointsDefinitionsAction()
    {
        $points = $this->getParameter('ruch_jow_points.types');

        return $this->createJsonResponse($points);
    }

    /**
     * @return Response
     *
     * @Route("/user_fvpc", name="page_foundation_cif_user_password_correct", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Method({"POST"})
     */
    public function checkUserPasswordCorrectAction()
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->validateRequestJson(
            array('type' => 'string',),
            $value
        );

        if (!$value) {
            return $this->createJsonResponse(true);
        }

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $ret     = $encoder->isPasswordValid($user->getPassword(), $value, $user->getSalt());

        return $this->createJsonResponse($ret);
    }

    /**
     * @param SocialLink[] $socialLinks
     *
     * @return array
     */
    protected function socialLinksToArray($socialLinks, $fullPath = false)
    {
        $services         = $this->getParameter('ruch_jow_social_links.services');
        $socialLinksArray = array();

        foreach ($socialLinks as $socialLink) {
            if (isset($services[$socialLink->getService()])) {
                $socialLinksArray[$socialLink->getService()] = $fullPath
                    ? $socialLink->getFullPath()
                    : $socialLink->getPath();
            }
        }

        return $socialLinksArray;
    }
}