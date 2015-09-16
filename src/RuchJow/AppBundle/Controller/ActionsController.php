<?php

namespace RuchJow\AppBundle\Controller;


use RuchJow\TaskBundle\Entity\Task;
use RuchJow\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\AppBundle\Controller
 *
 * @Route("/")
 */
class ActionsController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/ajax/organise_event", name="app_cif_organise_event", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function organiseEventAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type' => 'array',
                'children' => array(
                    'eventInfo' => array(
                        'type' => 'string',
                        'optional' => false,
                    ),
                ),
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->createJsonErrorResponse('Current user could not be get.');
        }

        if (!$user->getAddress()) {
            return $this->createJsonErrorResponse('supportForm.organise_event.send.error.message_empty_address');
        }

        $taskManager = $this->getTaskManager();
        $task = $taskManager->createTask();

        $taskTitle = $this->renderView(
            'RuchJowAppBundle:Actions:task.userSupportOrganiseEvent.title.txt.twig',
            array('user' => $user)
        );
        $taskContent = $this->renderView(
            'RuchJowAppBundle:Actions:task.userSupportOrganiseEvent.content.html.twig',
            array(
                'user' => $user,
                'eventInfo' => $data['eventInfo'],
            )
        );

        $task
            ->setType('user_support_organise_event')
            ->setTitle($taskTitle)
            ->setContent($taskContent)
        ;


        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush($task);


        $ruchJowMailPool = $this->getMailPool();
        $confirmMailTitle = $this->renderView(
            'RuchJowAppBundle:Actions:userSupportOrganiseEvent.confirm.mail.title.txt.twig',
            array('user' => $user)
        );
        $confirmMailContent = $this->renderView(
            'RuchJowAppBundle:Actions:userSupportOrganiseEvent.confirm.mail.content.html.twig',
            array(
                'user' => $user,
                'distInfo' => $data['eventInfo'],
            )
        );
        $ruchJowMailPool->sendMail(
            $user->getEmail(),
            $confirmMailTitle,
            $confirmMailContent
        );

        return $this->createJsonResponse('ok');
    }

    /**
     * @return Response
     *
     * @Route("/ajax/organise_referendum_point", name="app_cif_organise_referendum_point", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function organiseReferendumPointAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type' => 'array',
                'children' => array(
                    'organiseReferendumPointInfo' => array(
                        'type' => 'string',
                        'optional' => false,
                    ),
                ),
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->createJsonErrorResponse('Current user could not be get.');
        }

//        if (!$user->getAddress()) {
//            return $this->createJsonErrorResponse('supportForm.organise_referendum_point.send.error.message_empty_address');
//        }

        $taskManager = $this->getTaskManager();
        $task = $taskManager->createTask();

        $taskTitle = $this->renderView(
            'RuchJowAppBundle:Actions:task.userSupportOrganiseReferendumPoint.title.txt.twig',
            array('user' => $user)
        );
        $taskContent = $this->renderView(
            'RuchJowAppBundle:Actions:task.userSupportOrganiseReferendumPoint.content.html.twig',
            array(
                'user' => $user,
                'organiseReferendumPointInfo' => $data['organiseReferendumPointInfo'],
            )
        );

        $task
            ->setType('user_support_organise_referendum_point')
            ->setTitle($taskTitle)
            ->setContent($taskContent)
        ;


        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush($task);


        $ruchJowMailPool = $this->getMailPool();
        $confirmMailTitle = $this->renderView(
            'RuchJowAppBundle:Actions:userSupportOrganiseReferendumPoint.confirm.mail.title.txt.twig',
            array('user' => $user)
        );
        $confirmMailContent = $this->renderView(
            'RuchJowAppBundle:Actions:userSupportOrganiseReferendumPoint.confirm.mail.content.html.twig',
            array(
                'user' => $user,
                'organiseReferendumPointInfo' => $data['organiseReferendumPointInfo'],
            )
        );
        $ruchJowMailPool->sendMail(
            $user->getEmail(),
            $confirmMailTitle,
            $confirmMailContent
        );

        return $this->createJsonResponse('ok');
    }

    /**
     * @return Response
     *
     * // @Route("/ajax/distribute_leaflets", name="app_cif_distribute_leaflets", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function distributeLeafletsAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type' => 'array',
                'children' => array(
                    'distInfo' => array(
                        'type' => 'string',
                        'optional' => false,
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
        if (!$user) {
            return $this->createJsonErrorResponse('Current user could not be get.');
        }

        if (!$user->getAddress()) {
            return $this->createJsonErrorResponse('supportForm.distribute_leaflets.send.error.message_empty_address');
        }

        $taskManager = $this->getTaskManager();
        $task = $taskManager->createTask();

        $taskTitle = $this->renderView(
            'RuchJowAppBundle:Actions:task.userSupportDistributeLeaflets.title.txt.twig',
            array('user' => $user)
        );
        $taskContent = $this->renderView(
            'RuchJowAppBundle:Actions:task.userSupportDistributeLeaflets.content.html.twig',
            array(
                'user' => $user,
                'distInfo' => $data['distributeLeafletsInfo'],
            )
        );

        $task
            ->setType('user_support_distribute_leaflets')
            ->setTitle($taskTitle)
            ->setContent($taskContent)
        ;


        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush($task);

        $ruchJowMailPool = $this->getMailPool();
        $confirmMailTitle = $this->renderView(
            'RuchJowAppBundle:Actions:userSupportDistributeLeaflets.confirm.mail.title.txt.twig',
            array('user' => $user)
        );
        $confirmMailContent = $this->renderView(
            'RuchJowAppBundle:Actions:userSupportDistributeLeaflets.confirm.mail.content.html.twig',
            array(
                'user' => $user,
                'distInfo' => $data['distInfo'],
            )
        );
        $ruchJowMailPool->sendMail(
            $user->getEmail(),
            $confirmMailTitle,
            $confirmMailContent
        );

        return $this->createJsonResponse('ok');
    }

    /**
     * @return Response
     *
     * @Route("/ajax/local_gov_support", name="app_cif_local_gov_support", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function localGovSupportAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type' => 'array',
                'children' => array(
                    'eventInfo' => array(
                        'type' => 'string',
                        'optional' => false,
                    ),
                ),
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->createJsonErrorResponse('Current user could not be get.');
        }

        if (!$user->getAddress()) {
            return $this->createJsonErrorResponse('supportForm.local_gov_support.send.error.message_empty_address');
        }

        $taskManager = $this->getTaskManager();
        $task = $taskManager->createTask();

        $taskTitle = $this->renderView(
            'RuchJowAppBundle:Actions:task.userSupportLocalGovSupport.title.txt.twig',
            array('user' => $user)
        );
        $taskContent = $this->renderView(
            'RuchJowAppBundle:Actions:task.userSupportLocalGovSupport.content.html.twig',
            array(
                'user' => $user,
                'eventInfo' => $data['eventInfo'],
            )
        );

        $task
            ->setType('user_support_local_gov_support')
            ->setTitle($taskTitle)
            ->setContent($taskContent)
        ;


        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush($task);


        $ruchJowMailPool = $this->getMailPool();
        $confirmMailTitle = $this->renderView(
            'RuchJowAppBundle:Actions:userSupportLocalGovSupport.confirm.mail.title.txt.twig',
            array('user' => $user)
        );
        $confirmMailContent = $this->renderView(
            'RuchJowAppBundle:Actions:userSupportLocalGovSupport.confirm.mail.content.html.twig',
            array(
                'user' => $user,
                'distInfo' => $data['eventInfo'],
            )
        );
        $ruchJowMailPool->sendMail(
            $user->getEmail(),
            $confirmMailTitle,
            $confirmMailContent
        );

        return $this->createJsonResponse('ok');
    }
}
