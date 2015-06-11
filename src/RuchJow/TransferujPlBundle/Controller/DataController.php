<?php

namespace RuchJow\TransferujPlBundle\Controller;

use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserManager;
use RuchJow\UserBundle\Entity\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class DataController
 *
 * @package RuchJow\TransferujPlBundle\Controller
 *
 * @Route("/")
 */
class DataController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/encode", name="ruch_jow_transferuj_pl_encode", options={"expose"=true} )
     */
    public function encodeAction()
    {
        // Validate data.
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'id'     => array('type' => 'integer'),
                    'amount' => array('type' => 'decimal'),
                    'crc'    => array('type' => 'string'),
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        // Get security code
        $transferujPlUser = $this->getPaymentManager()->getAllowedUserById($data['id']);

        if (!$transferujPlUser) {
            return $this->createJsonErrorResponse('Transferuj.pl user not supported.');
        }
        $code = $transferujPlUser->getSecurityCode();

        // Encode md5sum
        $hash = md5($data['id'] . $data['amount'] . $data['crc'] . $code);

        return $this->createJsonResponse($hash);
    }


    /**
     * @route("/feedback", name="ruch_jow_transferuj_pl_handler")
     */
    public function handleFeedback(Request $request)
    {
        // Get acceptable transferuj.pl feedback ip.
        $feedbackIp = $this->getParameter('ruch_jow_transferuj_pl.feedback_ip');

        if ($request->getClientIp() !== $feedbackIp) {
            $this->createAccessDeniedException('Leave now and never come back! ... We... we told him to go away! And away he goes, preciousss.');
        };

        $ret = $this->getPaymentManager()->handleFeedback($request->request);

        // Transferuj.pl does not expect any HTTP headers - the easiest way is to just die.
        echo $ret ? 'TRUE' : 'FALSE';
        die();

//        return new Response($ret ? 'TRUE' : 'FALSE', 200);
    }
}