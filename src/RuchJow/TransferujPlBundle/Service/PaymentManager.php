<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 10/13/14
 * Time: 4:17 PM
 */

namespace RuchJow\TransferujPlBundle\Service;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\AbstractQuery;
use RuchJow\TransferujPlBundle\Entity\Payment;
use RuchJow\TransferujPlBundle\Entity\PaymentRepository;
use RuchJow\TransferujPlBundle\Entity\TransferujPlUser;
use RuchJow\TransferujPlBundle\Event\PaymentEvent;
use RuchJow\TransferujPlBundle\Event\PaymentUpdateEvent;
use RuchJow\TransferujPlBundle\TransferujPlEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class PaymentManager
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var PaymentRepository
     */
    protected $repository;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $feedbackIp;

    /**
     * @var TransferujPlUser[]
     */
    protected $allowedUsers;

    /**
     * @var string[]
     */
    protected $fields =
        array(
            'date',
            'crc',
            'amount',
            'paid',
            'description',
            'status',
            'error',
            'payersEmail'
        );

    public function __construct($dispatcher, Registry $doctrine, ContainerInterface $container)
    {
        $this->dispatcher    = $dispatcher;
        $this->entityManager = $doctrine->getManager();
        $this->repository    = $this->entityManager->getRepository('RuchJowTransferujPlBundle:Payment');
        $this->container     = $container;
    }

    /**
     * @return PaymentRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return TransferujPlUser[]
     */
    public function getAllowedUsers()
    {
        if (!$this->allowedUsers) {
            $this->allowedUsers = array();

            foreach ($this->container->getParameter('ruch_jow_transferuj_pl.allowed_users') as $data) {
                $this->allowedUsers[$data['id']] = new TransferujPlUser($data['id'], $data['security_code']);
            }
        }

        return $this->allowedUsers;
    }

    /**
     * @param $id
     *
     * @return TransferujPlUser|null
     */
    public function getAllowedUserById($id)
    {
        $allowedUsers = $this->getAllowedUsers();

        return isset($allowedUsers[$id]) ? $allowedUsers[$id] : null;
    }


    public function handleFeedback(ParameterBag $data, $flush = true)
    {

        // Check if transferuj.pl user is supported.
        $userId = $data->get('id');
        if (null === $userId) {
            return false;
        }

        $user = $this->getAllowedUserById($userId);
        if (!$user) {
            return false;
        }

        // Check if all expected params are present.
        $trId = $data->get('tr_id');
        try {
            $date = new \DateTime($data->get('tr_date'));
        } catch (\Exception $e) {
            return false;
        }

        $paymentData = array(
            'date'        => $date,
            'crc'         => $data->get('tr_crc'),
            'amount'      => $data->get('tr_amount'),
            'paid'        => $data->get('tr_paid'),
            'description' => $data->get('tr_desc'),
            'status'      => $data->get('tr_status'),
            'error'       => $data->get('tr_error'),
            'payersEmail' => $data->get('tr_email'),
            'md5sum'      => $data->get('md5sum'),
        );

        //
        if (
            null === $trId ||
            null === $paymentData['date'] ||
            null === $paymentData['crc'] ||
            null === $paymentData['amount'] ||
            null === $paymentData['paid'] ||
            null === $paymentData['description'] ||
            null === $paymentData['status'] ||
            null === $paymentData['error'] ||
            null === $paymentData['payersEmail'] ||
            null === $paymentData['md5sum']
        ) {
            return false;
        }

        // Verify hash.
        if ($paymentData['md5sum'] !== md5(
                $userId .
                $trId .
                $paymentData['amount'] .
                $paymentData['crc'] .
                $user->getSecurityCode()
            )) {
            return false;
        }

        $this->persistPayment($trId, $paymentData, $flush);

        return true;
    }

    public function persistPayment($transactionId, $paymentData, $flush = true)
    {
        /*$changed = false;*/

        // Get payment from db if it has been persisted already.
        /** @var Payment $payment */
        if (
            !$transactionId
            || !($payment = $this->repository->find($transactionId))
        ) {
            // Create new Payment entity if it has not been persisted previously.
            $payment = new Payment();
            $payment->setId($transactionId);

            // Fill all fields.
            foreach ($this->fields as $field) {
                $setterName = 'set' . ucfirst($field);
                $payment->$setterName($paymentData[$field]);
            }

            // Persist
            /*$changed = true;*/
            $this->entityManager->persist($payment);

            // Dispatch PAYMENT_NEW event.
            $event = new PaymentEvent($payment);
            $this->dispatcher->dispatch(TransferujPlEvents::PAYMENT_NEW, $event);

            // Dispatch PAYMENT_CONFIRMED event (if it's payed).
            if ($payment->isPayed()) {
                $this->dispatcher->dispatch(TransferujPlEvents::PAYMENT_CONFIRMED, $event);
            }

        } else {
            // Update fields and prepare array of changes.
            $changeBag = array();

            foreach ($this->fields as $field) {
                $getterName = 'get' . ucfirst($field);
                $oldV       = $payment->$getterName();
                $newV       = $paymentData[$field];
                if ($oldV != $newV) {
                    $changeBag[$field] = array($oldV, $newV);
                    $setterName        = 'set' . ucfirst($field);
                    $payment->$setterName($newV);
                }
            }

            // If anything has changed...
            if (!empty($changeBag)) {

                // ... persist updated payment entity.
                /*$changed = true;*/
                $this->entityManager->persist($payment);

                // Dispatch PAYMENT_UPDATED event.
                $event = new PaymentUpdateEvent($payment, $changeBag);
                $this->dispatcher->dispatch(TransferujPlEvents::PAYMENT_UPDATED, $event);

                if ($payment->isPayed()) {
                    if (!isset($changeBag['status'])) {

                        // Dispatch PAYMENT_CONFIRMED_UPDATE event (payment was already confirmed but it has changed).
                        $this->dispatcher->dispatch(TransferujPlEvents::PAYMENT_CONFIRMED_UPDATE, $event);

                    } elseif ($changeBag['status'][1]) {

                        // Dispatch PAYMENT_CONFIRMED_UPDATE event (payment has been updated and it is now payed)
                        $this->dispatcher->dispatch(TransferujPlEvents::PAYMENT_CONFIRMED, new PaymentEvent($payment));

                    }
                }

                // TODO Dispatch event if payment was confirmed and it isn't now.
            }
        }

        // Flush if it is expected /*and needed*/.
        if ($flush/* && $changed*/) {
            $this->entityManager->flush();
        }

    }



    /**
     * @param string|integer $dateIntervalStr
     * @param null           $types
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return integer
     */
    public function getPaymentStats($dateIntervalStr = null, $types = null) {

        /** @var PaymentRepository $repo */
        $repo = $this->repository;

        $qb = $repo->createQueryBuilder('p');
        $qb->select('coalesce(sum(p.paid), 0) total, count(distinct p.id) cnt')
            ->where($qb->expr()->eq('p.status', ':status'))
            ->setParameter('status', 'TRUE');

        if ($dateIntervalStr) {
            if (is_integer($dateIntervalStr)) {
                $dateIntervalStr = 'P' . $dateIntervalStr . 'D';
            }

            $interval = new \DateInterval($dateIntervalStr);
            $date = new \DateTime();
            $date->setTime(0, 0, 0)->sub($interval);

            $qb
                ->andWhere($qb->expr()->gte('p.date', ':minDate'))
                ->setParameter('minDate', $date);
        }

        if ($types) {
            if (!is_array($types)) {
                $types = array($types);
            }

            $qb->andWhere($qb->expr()->in('p.type', $types));
        }

        $query = $qb->getQuery()->getSQL();
        $ret = $qb->getQuery()->getSingleResult(AbstractQuery::HYDRATE_SCALAR);

        $ret['total'] = (float) $ret['total'];
        $ret['cnt'] = (int) $ret['cnt'];

        return $ret;
    }

}