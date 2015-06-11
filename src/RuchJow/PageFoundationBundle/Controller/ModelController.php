<?php

namespace RuchJow\PageFoundationBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use RuchJow\JsonValidatorBundle\Services\JsonValidator;
use Ruchjow\MailPoolBundle\Service\MailPool;
use RuchJow\PointsBundle\Services\PointsManager;
use RuchJow\StatisticsBundle\Entity\StatisticManager;
use RuchJow\TaskBundle\Services\TaskManager;
use RuchJow\UserBundle\Entity\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ModelController - provides basic helper functions.
 *
 * @package RuchJow\PageFoundationBundle\Controller
 */
abstract class ModelController extends Controller
{

    /**
     * @param string $paramName
     *
     * @return mixed
     */
    protected function getParameter($paramName)
    {
        return $this->container->getParameter($paramName);
    }

    /**
     * @return Router
     */
    protected function getRouter()
    {
        return $this->get('router');
    }

    /**
     * @return PointsManager
     */
    protected function getPointsManager()
    {
        return $this->get('ruch_jow_points.points_manager');
    }


    /**
     * @return StatisticManager
     */
    public function getStatisticManager()
    {
        return $this->get('ruch_jow_statistics.statistic_manager');
    }


    /**
     * @return UserManager
     */
    protected function getUserManager()
    {
        return $this->get('fos_user.user_manager');
    }

    protected function isUserLoggedIn()
    {
        $securityContext = $this->container->get('security.context');

        return $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    /**
     * @return SecurityContext
     */
    protected function getSecurityContext()
    {
        return $this->get('security.context');
    }

    /**
     * @param string|array $attributes
     * @param null         $object
     *
     * @return bool
     */
    protected function isGranted($attributes, $object = null)
    {
        return $this->getSecurityContext()->isGranted($attributes, $object);
    }

    /**
     * @return MailPool
     */
    protected function getMailPool() {
        return $this->get('ruch_jow_mail_pool.mail_pool');
    }

    /**
     * @return JsonValidator;
     */
    protected function getDataValidator()
    {
        return $this->get('ruch_jow_json_validator.json_validator');
    }


    /**
     * @param mixed  $data
     * @param array  $dataDef
     * @param array  &$error
     * @param string $name
     *
     * @return bool
     */
    protected function validateData($data, $dataDef, &$error = array(), $name = 'data')
    {
        $validator = $this->getDataValidator();

        return $validator->validate($data, $dataDef, $error, $name);
    }


    protected function getRequestJsonData() {
        /** @var $request Request */
        $request = $this->get('request_stack')->getCurrentRequest();
        $content = $request->getContent();

        return json_decode($content, true);
    }


    /**
     * @param array $dataDef
     * @param mixed &$data
     *
     * @return null|array
     */
    protected function validateRequestJson($dataDef, &$data)
    {
        /** @var $request Request */
        $request = $this->get('request_stack')->getCurrentRequest();
        $content = $request->getContent();

        if (empty($content)) {
            return array('message' => 'No data provided.');
        }

        $data = json_decode($content, true);
        $error = array();

        if (!$this->validateData($data, $dataDef, $error)) {
            return $error;
        }

        return null;
    }

    protected function getPostJson(&$errorMsg = array())
    {
        /** @var $request Request */
        $request = $this->get('request_stack')->getCurrentRequest();
        $content = $request->getContent();

        if (empty($content)) {
            $errorMsg = 'No data provided.';
            return null;
        }

        return json_decode($content, true);
    }

    /**
     * @param mixed $data
     * @param int   $status
     *
     * @return Response
     */
    protected function createJsonResponse($data, $status = 200)
    {
        return new Response(
            json_encode($data),
            $status,
            array(
                'Content-Type' => 'application/json',
            )
        );
    }

    /**
     * Creates response with json encoded message.
     *
     * @param string $message
     * @param int    $code
     *
     * @return Response
     */
    protected function createJsonErrorResponse($message, $code = 500)
    {
        return $this->createJsonResponse(
            array('message' => $message),
            $code
        );
    }

    /**
     * Creates unauthorized (401) or access denied (403) response depending on whether user is logged in or not.
     *
     * @param      $msgUnauthorized
     * @param null $msgAccessDenied
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createJsonErrorNotGrantedResponse($msgUnauthorized, $msgAccessDenied = null)
    {
        if ($this->isUserLoggedIn()) {

            // 403 - Access Denied
            return $this->createJsonErrorResponse(
                null !== $msgAccessDenied ? $msgAccessDenied : $msgUnauthorized,
                403
            );
        } else {

            // 401 - Unauthorized
            return $this->createJsonErrorResponse(
                $msgUnauthorized,
                401
            );
        }
    }

    /**
     * @param string $message
     * @param int    $code
     *
     * @return Response
     *
     * @deprecated
     */
    protected function getErrorResponse($message, $code = 500)
    {
        return $this->createJsonErrorResponse($message, $code);
    }

    /**
     * @param string $repo
     *
     * @return ObjectRepository
     */
    protected function getRepository($repo)
    {
        return $this
            ->getDoctrine()
            ->getManager()
            ->getRepository($repo);
    }


    /**
     * @return TaskManager
     */
    protected function getTaskManager()
    {
        return $this->get('ruch_jow_task.task_manager');
    }
}