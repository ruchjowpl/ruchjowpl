<?php

namespace RuchJow\BackendBundle\Controller;


use RuchJow\AddressBundle\Entity\Address;
use RuchJow\FeedbackBundle\Entity\Feedback;
use RuchJow\TaskBundle\Entity\Task;
use RuchJow\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\BackendBundle\Controller
 *
 * @Route("/")
 */
class DefaultController extends ModelController
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/", name="backend_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array();
    }


    /**
     * @return Response
     *
     * @Route("/cif/user_roles", name="backend_cif_user_roles", options={"expose"=true} )
     */
    public function getUserRolesAction()
    {
        $exposedRoles = $this->container
            ->getParameter('ruch_jow_backend.exposed_user_roles');

        $userRoles = array();
        foreach ($exposedRoles as $role) {
            if ($this->isGranted($role)) {
                $userRoles[$role] = true;
            }
        }

        return $this->createJsonResponse($userRoles);
    }






    /**
     * @return Response
     *
     * @Route("/cif/feedback", name="backend_cif_feedback",  options={"expose": true})
     */
    public function feedbackDataAction(/*Request $request*/)
    {
        /** @var Feedback[] $feedbackArray */
        $feedbackArray = $this
            ->getRepository('RuchJowFeedbackBundle:Feedback')
            ->findBy(array(), array('date' => 'desc'));

        $ret = array();
        foreach ($feedbackArray as $feedback) {

            $ret[] = array(
                'date'        => $feedback->getDate()->format('D M d Y H:i:s O'),
                'title'       => $feedback->getTitle(),
                'description' => $feedback->getDescription(),
                'ip'          => $feedback->getIp(),
                'nick'        => $feedback->getNick(),
                'contact'     => $feedback->getContact(),
            );
        }

        return $this->createJsonResponse($ret);
    }

    /**
     * @return Response
     *
     * @Route("/cif/tasks", name="backend_cif_tasks", options={"expose": true})
     */
    public function tasksDataAction(/*Request $request*/)
    {
        $repo = $this->getTaskManager()->getTaskRepository();

        /** @var Task[] $tasks */
        $tasks = $repo->findAll();

        $ret = array();
        foreach ($tasks as $task) {
            $ret[] = array(
//                'user' => $task->getAssignedTo(),
                'title' => $task->getTitle(),
                'content' => $task->getContent(),
                'type' => $task->getType(),
                'createdAt' => $task->getCreatedAt() ? $task->getCreatedAt()->format('D M d Y H:i:s O') : null,
                'canceledAt' => $task->getCanceledAt() ? $task->getCanceledAt()->format('D M d Y H:i:s O') : null,
            );
        }

        return $this->createJsonResponse($ret);
    }


//    /**
//     * @return Response
//     *
//     * @Route("/cif/users/unverified", name="backend_cif_users_unverified", options={"expose": true})
//     */
//    public function usersUnverifiedAction(/*Request $request*/)
//    {
//        $repo = $this->getUserManager()->getRepository();
//
//        $qb = $repo->createQueryBuilder('u');
//        $qb->where($qb->expr()->eq('u.supports', 0));
//
//        /** @var User[] $users */
//        $users = $qb->getQuery()->getResult();
//
//        $ret = array();
//        foreach ($users as $user) {
//            $ret[] = array(
//                'id' => $user->getId(),
//                'nick' => $user->getNick(),
//                'email' => $user->getEmail(),
//                'link' => $this->getUserManager()->getConfirmationLink($user),
//                'createdAt' => $user->getCreatedAt() ? $user->getCreatedAt()->format('D M d Y H:i:s O') : null
//            );
//        }
//
//        return $this->createJsonResponse($ret);
//    }
//
//    /**
//     * @param $name
//     *
//     * @return Response
//     *
//     * @Route("/cif/user/data/{name}", name="backend_cif_user_data", options={"expose": true})
//     */
//    public function userDataAction($name/*, Request $request*/)
//    {
//        $manager = $this->getUserManager();
//
//        /** @var User $user */
//        $user = $manager->findUserByUsername($name);
//        if (!$user) {
//            return $this->createJsonErrorResponse('User not found', 404);
//        }
//
//        return $this->createJsonResponse(
//            $this->userToArray($user)
//        );
//    }
//
//
//    /**
//     * @param User $user
//     *
//     * @return array
//     */
//    protected function userToArray($user)
//    {
//        return array(
//            'nick' => $user->getNick() ? $user->getNick() : $user->getUsername(),
//            'email' => $user->getEmail(),
//            'address' => $user->getAddress() ? $user->getAddress()->toArray() : null,
//            'commune' => $user->getCommune() ? $user->getCommune()->toArray() : null,
//            'firstName' => $user->getFirstName(),
//            'lastName' => $user->getLastName(),
//            'organisation' => $user->getOrganisation() ? $user->getOrganisation()->toArray() : null,
//            'phone' => $user->getPhone(),
//            'supports' => $user->isSupports(),
//            'createdAt' => $user->getCreatedAt() ? $user->getCreatedAt()->format('D M d Y H:i:s O') : null,
//            'supportedAt' => $user->getSupportedAt() ? $user->getSupportedAt()->format('D M d Y H:i:s O') : null,
//            'confirmationLink' => $this->getUserManager()->getConfirmationLink($user),
//        );
//    }




}
