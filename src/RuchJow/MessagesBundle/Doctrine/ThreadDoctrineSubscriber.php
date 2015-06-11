<?php

namespace RuchJow\MessagesBundle\Doctrine;


use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RuchJow\MessagesBundle\Entity\Message;
use RuchJow\MessagesBundle\Entity\Thread;
use RuchJow\MessagesBundle\Event\MessageChangeEvent;
use RuchJow\MessagesBundle\Event\ThreadChangeEvent;
use RuchJow\MessagesBundle\MessageEvents;
use RuchJow\MessagesBundle\ThreadEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ThreadDoctrineSubscriber implements EventSubscriber
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $data = array();

    protected $watchedFields = array(
        'threadsParent',
    );

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postRemove',
            'preUpdate',
            'postPersist',
            'postFlush',
        );
    }


    public function postRemove(LifecycleEventArgs $args){
        // FIXME Chenge set must be prepared.
        $entity = $args->getObject();
        if ($entity instanceof Thread) {
            $data = array(
                'entity'    => null,
                'changeSet' => array()
            );

            $this->data[$entity->getId()] = $data;
        }
    }

    /**
     * @param LifecycleEventArgs|PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Thread) {

            $changeSet = $args->getEntityChangeSet();
            if (!empty($changeSet)) {
                $data = array(
                    'entity'    => $entity,
                    'changeSet' => array(),
                );

                foreach ($this->watchedFields as $fieldName) {
                    if ($args->hasChangedField($fieldName)) {
                        $data['changeSet'][$fieldName] = $args->getOldValue($fieldName);
                    }
                }
                $this->data[$entity->getId()] = $data;
            };
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Thread) {
            $data = array(
                'entity'    => $entity,
                'changeSet' => array()
            );

            foreach ($this->watchedFields as $fieldName) {
                $data['changeSet'][$fieldName] = null;
            }

            $this->data[$entity->getId()] = $data;
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->handleFlush($args->getEntityManager());
    }


    /**
     * Dispatches THREAD_CHANGED event (for each modified or created thread entity).
     */
    protected function handleFlush(EntityManager $em)
    {
        $data           = $this->data;
        $this->data     = array();

        $dispatched = false;
        foreach ($data as $dataElem) {

            if ($dataElem['entity']) {
                $this->dispatcher->dispatch(
                    ThreadEvents::THREAD_CHANGED,
                    new ThreadChangeEvent($dataElem['entity'], $dataElem['changeSet'])
                );
            } else {
                $this->dispatcher->dispatch(
                    ThreadEvents::THREAD_REMOVED,
                    new ThreadChangeEvent($dataElem['entity'], $dataElem['changeSet'])
                );
            }

            $dispatched = true;
        }

        if ($dispatched) {
            $em->flush();
        }
    }

}