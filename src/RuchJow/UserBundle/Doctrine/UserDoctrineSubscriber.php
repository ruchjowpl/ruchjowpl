<?php

namespace RuchJow\UserBundle\Doctrine;


use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Event\UserChangeEvent;
use RuchJow\UserBundle\Event\UserLifecycleEvent;
use RuchJow\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserDoctrineSubscriber implements EventSubscriber
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $userData = array();

    protected $watchedFields = array(
        'commune',
        'organisation',
        'localGov',
        'supports',
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
            'prePersist',
            'preUpdate',
            'postPersist',
            'postUpdate',
            'postFlush',
        );
    }


    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof User) {

            // Dispatch user pre persist event.
            $this->dispatcher->dispatch(
                UserEvents::USER_PRE_PERSIST,
                new UserLifecycleEvent($entity, $args)
            );


        }
    }


    /**
     * @param LifecycleEventArgs|PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof User) {
            $this->dispatcher->dispatch(
                UserEvents::USER_PRE_UPDATE,
                new UserLifecycleEvent($entity, $args)
            );

            $changeSet = $args->getEntityChangeSet();
            if (!empty($changeSet)) {
                $userData = array(
                    'entity'    => $entity,
                    'changeSet' => array(),
                );

                foreach ($this->watchedFields as $fieldName) {
                    if ($args->hasChangedField($fieldName)) {
                        $userData['changeSet'][$fieldName] = $args->getOldValue($fieldName);
                    }
                }
                $this->userData[$entity->getId()] = $userData;


                // Dispatch USER_PRE_UPDATE event.
                $this->dispatcher->dispatch(
                    UserEvents::USER_PRE_UPDATE,
                    new UserLifecycleEvent($entity, $args)
                );

                // Recompute change set
                // (it is only necessary if entity has been change during USER_PRE_UPDATE event)
                $em   = $args->getEntityManager();
                $uow  = $em->getUnitOfWork();
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            };
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof User) {
            $userData = array(
                'entity'    => $entity,
                'changeSet' => array()
            );

            foreach ($this->watchedFields as $fieldName) {
                $userData['changeSet'][$fieldName] = null;
            }

            $this->userData[$entity->getId()] = $userData;

            // Dispatch USER_POST_PERSIST event.
            $this->dispatcher->dispatch(
                UserEvents::USER_POST_PERSIST,
                new UserChangeEvent($entity, $userData['changeSet'])
            );
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof User) {

            if (isset($this->userData[$entity->getId()])) {
                // Dispatch USER_POST_UPDATE event.
                $this->dispatcher->dispatch(
                    UserEvents::USER_POST_UPDATE,
//                    new UserLifecycleEvent($entity, $args)
                    new UserChangeEvent($entity, $this->userData[$entity->getId()]['changeSet'])
                );
            }

        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->handleFlush();
    }


    /**
     * Dispatches USER_CHANGED event (for each modified or created user entity).
     */
    protected function handleFlush()
    {
        $userData       = $this->userData;
        $this->userData = array();

        foreach ($userData as $data) {
            $this->dispatcher->dispatch(
                UserEvents::USER_CHANGED,
                new UserChangeEvent($data['entity'], $data['changeSet'])
            );
        }
    }

}