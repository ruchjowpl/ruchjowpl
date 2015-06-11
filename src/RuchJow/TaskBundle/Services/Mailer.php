<?php

namespace RuchJow\TaskBundle\Services;

use Ruchjow\MailPoolBundle\Service\MailPool;
use RuchJow\TaskBundle\Entity\Task;
use RuchJow\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RouterInterface;

class Mailer
{
    protected $ruchJowMailPool;
    protected $templating;
    protected $container;
    protected $parameters;

    public function __construct(MailPool $ruchJowMailPool, EngineInterface $templating, Container $container, array $parameters)
    {
        $this->ruchJowMailPool = $ruchJowMailPool;
        $this->templating = $templating;
        $this->container  = $container;
        $this->parameters = $parameters;
    }

    /**
     * @param Task    $task
     * @param User[]  $users
     * @param boolean $justCreated
     * @param boolean $justCanceled
     */
    public function sendTaskInfo($task, $users, $justCreated, $justCanceled)
    {
        $emails = array();
        foreach ($users as $user) {
            $emails[] = $user->getEmail();
        }

        $template = $this->parameters['task_info.subject_template'];
        $subject = $this->templating->render($template, array(
            'task'            => $task,
            'justCreated'     => $justCreated,
            'justCanceled'     => $justCanceled,
        ));

        $from     = $this->container->getParameter('ruch_jow_task.mailer.from');
        $fromName = $this->container->getParameter('ruch_jow_task.mailer.from_name');

        // Body
        $template = $this->parameters['task_info.template'];
        $body     = $this->templating->render($template, array(
            'task'            => $task,
            'justCreated'     => $justCreated,
            'justCanceled'     => $justCanceled,
        ));

        $this->sendEmailMessage(
            $emails,
            $subject,
            $body,
            $from,
            $fromName
        );
    }

    /**
     * @param        $recipient
     * @param string $subject
     * @param string $body
     * @param string $from
     * @param        $fromName
     */
    protected function sendEmailMessage($recipient, $subject, $body, $from, $fromName)
    {
        $this->ruchJowMailPool->sendMail(
            $recipient,
            $subject,
            $body,
            null,
            true,
            $from,
            $fromName
        );
    }
}