<?php

namespace RuchJow\BackendBundle\Controller;


use RuchJow\TransferujPlBundle\Service\PaymentManager;
use RuchJow\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\BackendBundle\Controller
 *
 * @Route("/")
 */
class UserController extends ModelController
{


    /**
     * @return Response
     *
     * @Route("/cif/users/unverified", name="backend_cif_users_unverified", options={"expose": true}, condition="request.isXmlHttpRequest()")
     */
    public function usersUnverifiedAction(/*Request $request*/)
    {
        $repo = $this->getUserManager()->getRepository();

        $qb = $repo->createQueryBuilder('u');
        $qb->where($qb->expr()->eq('u.supports', 0));

        /** @var User[] $users */
        $users = $qb->getQuery()->getResult();

        $ret = array();
        foreach ($users as $user) {
            $ret[] = array(
                'id'        => $user->getId(),
                'nick'      => $user->getNick(),
                'email'     => $user->getEmail(),
                'link'      => $this->getUserManager()->getConfirmationLink($user),
                'createdAt' => $user->getCreatedAt() ? $user->getCreatedAt()->format('D M d Y H:i:s O') : null
            );
        }

        return $this->createJsonResponse($ret);
    }

    /**
     * @return Response
     *
     * @Route("/cif/user/search", name="backend_cif_user_search", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function searchUsers()
    {
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'search'      => array('type' => 'string',),
                    'maxElements' => array(
                        'type'     => 'integer',
                        '>'        => 0,
                        'optional' => true,
                    )
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $minLength = $this->getParameter('ruch_jow_backend.user.search.min_length');
        $minLengthAll = $this->getParameter('ruch_jow_backend.user.search.min_length_all');
        $maxElements = isset($data['maxElements'])
            ? min(
                $data['maxElements'],
                $this->getParameter('ruch_jow_backend.user.search.max_elements')
            )
            : $this->getParameter('ruch_jow_backend.user.search.max_elements');

        $users = $this->getUserManager()->getRepository()->searchUsers(
            $data['search'],
            true,
            true,
            $maxElements,
            $minLength,
            $minLengthAll,
            true
        );

        $ret = array();
        foreach ($users as $user) {
            $ret[] = array(
                'nick' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            );
        }

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data' => $ret,
        ));
    }


    /**
     * @param $name
     *
     * @return Response
     *
     * @Route("/cif/user/data/{name}", name="backend_cif_user_data", options={"expose": true}, condition="request.isXmlHttpRequest()")
     */
    public function userDataAction($name/*, Request $request*/)
    {
        $manager = $this->getUserManager();

        /** @var User $user */
        $user = $manager->findUserByUsername($name);
        if (!$user) {
            return $this->createJsonErrorResponse('User not found', 404);
        }

        return $this->createJsonResponse(
            $this->userToArray($user)
        );
    }


    /**
     * @param User $user
     *
     * @return array
     */
    protected function userToArray($user)
    {
        return array(
            'nick'             => $user->getNick() ? $user->getNick() : $user->getUsername(),
            'email'            => $user->getEmail(),
            'address'          => $user->getAddress() ? $user->getAddress()->toArray() : null,
            'commune'          => $user->getCommune() ? $user->getCommune()->toArray() : null,
            'firstName'        => $user->getFirstName(),
            'lastName'         => $user->getLastName(),
            'organisation'     => $user->getOrganisation() ? $user->getOrganisation()->toArray() : null,
            'phone'            => $user->getPhone(),
            'supports'         => $user->isSupports(),
            'createdAt'        => $user->getCreatedAt() ? $user->getCreatedAt()->format('D M d Y H:i:s O') : null,
            'supportedAt'      => $user->getSupportedAt() ? $user->getSupportedAt()->format('D M d Y H:i:s O') : null,
            'confirmationLink' => $this->getUserManager()->getConfirmationLink($user),
        );
    }


    /**
     * @return Response
     *
     * @Route("/cif/user/points_add_options", name="backend_cif_points_add_options", options={"expose": true}, condition="request.isXmlHttpRequest()")
     */
    public function userPointsAddOptionsAction(/*Request $request*/)
    {
        $pointTypes = $this->getParameter('ruch_jow_points.types');

        $options = array();


        foreach ($pointTypes as $type => $definition) {
            if ($definition['manual']) {
                $options[$type]         = $definition;
                $options[$type]['type'] = $type;
            }
        }

        return $this->createJsonResponse($options);
    }


    /**
     * @return Response
     *
     * @Route("/cif/user/points_add", name="backend_cif_user_points_add", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function userPointsAddAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'username'    => array('type' => 'string'),
                    'type'        => array('type' => 'string'),
                    'points'      => array('type' => 'integer'),
                    'date'        => array('type' => 'date'),
                    'description' => array('type' => 'string')
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $pointTypes = $this->getParameter('ruch_jow_points.types');
        if (!isset($pointTypes[$data['type']])) {
            return $this->createJsonErrorResponse('Incorrect points type.');
        }

        $definition = $pointTypes[$data['type']];

        if (
            $data['points'] <= 0
            || isset($definition['min'])
            && $data['points'] < $definition['min']
        ) {
            return $this->createJsonErrorResponse('Not enough points.');
        }
        if (
            isset($definition['max'])
            && $data['points'] > $definition['max']
        ) {
            return $this->createJsonErrorResponse('To many points.');
        }

        $date = new \DateTime($data['date']);
        $now  = new \DateTime();
        if ($date > $now->add(new \DateInterval('P1D'))) {
            return $this->createJsonErrorResponse('Date from the future.');
        }

        /** @var User $user */
        $user = $this->getUserManager()->findUserByUsername($data['username']);
        if (!$user) {
            return $this->createJsonErrorResponse('User not found!');
        }

        $this->getPointsManager()->addPoints(
            $user,
            $data['type'],
            $data['points'],
            array('description' => $data['description'])
        );

        return $this->createJsonResponse('ok');
    }

    /**
     * @return Response
     *
     * @Route("/cif/user/donation_add", name="backend_cif_user_donation_add", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function userDonationAddAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'username' => array('type' => 'string'),
                    'amount'   => array(
                        'type' => 'integer',
                        '>'    => 1,
                    ),
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        /** @var User $user */
        $user = $this->getUserManager()->findUserByUsername($data['username']);
        if (!$user) {
            return $this->createJsonErrorResponse('User not found!');
        }

        $now           = new \DateTime();
        $transactionId = 'MANUAL-DONATION-' . $now->format('Y-m-d-H-i-s') . '-' . rand();

        /** @var PaymentManager $paymentManager */
        $paymentManager = $this->get('ruch_jow_transferuj_pl.payment_manager');
        $paymentManager->persistPayment(
            $transactionId,
            array(
                'date'        => $now,
                'crc'         => json_encode(array(
                    'type' => 'donation',
                    'user' => $user->getId(),
                )),
                'amount'      => floatval($data['amount']),
                'paid'        => floatval($data['amount']),
                'description' => 'Wpłata na rzecz akcji Ruch JOW',
                'status'      => 'TRUE',
                'error'       => 0,
                'payersEmail' => $user->getEmailCanonical(),
            )
        );

        return $this->createJsonResponse(array('status' => 'success'));
    }


}
