<?php

namespace RuchJow\TaskBundle\Services;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use RuchJow\TaskBundle\Entity\Task;
use RuchJow\TaskBundle\Entity\TaskStatus;
use RuchJow\TaskBundle\Entity\TaskStatusRepository;
use RuchJow\TaskBundle\Entity\TaskTypeMapRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TaskManager
{

    /**
     * @var EntityManager
     *
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     *
     */
    protected $dispatcher;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var TaskStatus
     */
    protected $defaultStatus;

    public function __construct(Registry $doctrine, EventDispatcherInterface $dispatcher, ContainerInterface $container, Mailer $mailer)
    {
        $this->entityManager = $doctrine->getManager();
        $this->dispatcher    = $dispatcher;
        $this->container     = $container;
        $this->mailer        = $mailer;
    }

    /**
     * @return Task
     */
    public function createTask() {

        $task = new Task();
        $task->setStatus($this->getDefaultStatus());

        return $task;
    }

    /**
     * @param Task $task
     */
    public function updateTask(Task $task)
    {
        $now = new \DateTime();

        if (!$task->getCreatedAt()) {
            $task->setCreatedAt($now);
        }


        // Set canceledAt.
        if (
            $task->getStatus()->isCanceled()
            && !$task->getCanceledAt()
        ) {
            $task->setCreatedAt($now);
        }

        // Set readAt.
        if (
            !$task->getStatus()->isNew()
            && !$task->getStatus()->isCanceled()
            && !$task->getReadAt()
        ) {
            $task->setReadAt($now);
        }

        // Set closedAt.
        if (
            $task->getStatus()->isClosed()
            && !$task->getClosedAt()
        ) {
            $task->setClosedAt($now);
        }
    }

    /**
     * @return TaskStatus
     */
    public function getDefaultStatus() {

        if (!$this->defaultStatus) {
            $defaultStatusName = $this->container->getParameter('ruch_jow_task.default_task_status');

            $repo = $this->getTaskStatusRepository();
            $this->defaultStatus = $repo->find($defaultStatusName);
        }

        return $this->defaultStatus;
    }

    /**
     * @return TaskStatusRepository
     */
    public function getTaskStatusRepository()
    {
        return $this->entityManager->getRepository('RuchJowTaskBundle:TaskStatus');
    }

    /**
     * @return TaskStatusRepository
     */
    public function getTaskRepository()
    {
        return $this->entityManager->getRepository('RuchJowTaskBundle:Task');
    }

    /**
     * @return TaskStatusRepository
     */
    public function getTaskTypeMapRepository()
    {
        return $this->entityManager->getRepository('RuchJowTaskBundle:TaskTypeMap');
    }


    /**
     * @param Task $task
     *
     * @return array
     */
    public function findRelatedUsers($task)
    {
        /** @var TaskTypeMapRepository $repo */
        $repo = $this->getTaskTypeMapRepository();

        return $repo->findByType($task->getType(), true);
    }

    public function sendTaskInfo(Task $task, $justCreated, $justCanceled)
    {
        $this->mailer->sendTaskInfo($task, $this->findRelatedUsers($task), $justCreated, $justCanceled);
    }

}