<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 9/8/14
 * Time: 10:58 AM
 */

namespace RuchJow\PointsBundle\Services;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use RuchJow\PointsBundle\Entity\PointsEntry;
use RuchJow\PointsBundle\Entity\PointsEntryRepository;
use RuchJow\PointsBundle\Event\PointsEvent;
use RuchJow\PointsBundle\PointsEvents;
use RuchJow\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PointsManager
{

    /**
     * @var EntityManager
     *
     */
    protected $entityManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $types;

    protected $pointsRepository;

    public function __construct(Registry $doctrine, EventDispatcherInterface $eventDispatcher, ContainerInterface $container)
    {
        $this->entityManager = $doctrine->getManager();
        $this->container     = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Add new points to a user.
     *
     * @param User           $user
     * @param string         $type
     * @param                $points
     * @param null|mixed     $additionalData
     * @param boolean        $flush *
     * @param \DateTime|null $date
     *
     * @return $this
     * @throws \Exception
     */
    public function addPoints(User $user, $type, $points = null, $additionalData = null, $flush = true, $date = null)
    {
        $pointsEntry = new PointsEntry();
        $pointsEntry
            ->setUser($user)
            ->setType($type)
            ->setOrganisation($user->getOrganisation())
            ->setCommune($user->getCommune())
            ->setData($additionalData);

        if ($date) {
            $pointsEntry->setDate($date);
        }

        if (!$points) {
            $typeData = $this->getType($type);
            if (!$typeData) {
                throw new \Exception('Points type ' . $type . ' is not predefined. You can add this type definition '
                    . 'to bundle configuration or pass arbitrary points amount.');
            }

            $points = $typeData['points'];
        }

        $pointsEntry->setPoints($points);

        $this->entityManager->persist($pointsEntry);

        // Dispatch Event
        $this->eventDispatcher->dispatch(PointsEvents::POINTS_ADD_EVENT, new PointsEvent($pointsEntry));

        if ($flush) {
            $this->entityManager->flush();
        }

        return $this;
    }

    /**
     * @return PointsEntryRepository
     */
    public function getRepository()
    {
        if (!$this->pointsRepository) {
            $this->pointsRepository =
                $this->entityManager
                    ->getRepository('RuchJowPointsBundle:PointsEntry');
        }

        return $this->pointsRepository;
    }

    public function getType($type)
    {
        if (!$this->types) {
            $this->types = $this->container->getParameter('ruch_jow_points.types');
        }

        return isset($this->types[$type]) ? $this->types[$type] : null;
    }

    public function getPointsByType($type)
    {
        $typeDef = $this->getType($type);

        if ($typeDef) {
            return $typeDef['points'];
        }

        return 0;
    }

    /**
     * @param $uId
     *
     * @return PointsEntry[]
     */
    public function getPointsByUser($uId)
    {
        $repo = $this->getRepository();

        $qb = $repo->createQueryBuilder('p');

        $qb->join('p.user', 'u')
            ->where($qb->expr()->eq('u.id', ':uid'))
            ->setParameter('uid', $uId);

        $points = $qb->getQuery()->getResult();

        return $points;
    }


}