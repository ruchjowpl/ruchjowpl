<?php

namespace RuchJow\FeedbackBundle\Controller;


use RuchJow\FeedbackBundle\Entity\Feedback;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\UserBundle\Controller
 *
 * @Route("/")
 */
class FeedbackController extends ModelController
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/create", name="feedback_ajax_create", options={"expose": true})
     * @Method("POST")
     */
    public function createFeedbackAction(Request $request)
    {
        // Get POSTed Json and validate its format.
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'nick'             => array('type' => 'string', 'optional' => true),
                    'title'            => array('type' => 'string'),
                    'description'      => array('type' => 'string', 'optional' => true),
                    'contact'      => array('type' => 'string', 'optional' => true),
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $feedback = new Feedback();
        $feedback
            ->setTitle($data['title'])
            ->setDescription(isset($data['description']) ? $data['description'] : '')
            ->setContact(isset($data['contact']) ? $data['contact'] : '')
            ->setDate(new \DateTime());

        $user = $this->getUser();

        if (!$user) {

            if (!isset($data['nick'])) {
                return $this->createJsonErrorResponse('Nick is required when user id not logged in.');
            }

            $feedback
                ->setIp($request->getClientIp())
                ->setNick($data['nick']);
        } else {
            $feedback
                ->setUser($user);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($feedback);
        $em->flush();

        return $this->createJsonResponse('ok');
    }

}
