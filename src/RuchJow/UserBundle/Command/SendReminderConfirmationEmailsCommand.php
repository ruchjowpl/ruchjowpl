<?php

namespace RuchJow\UserBundle\Command;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserRepository;
use RuchJow\UserBundle\Services\Mailer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Resend e-mail registration after 1, 3, 7 days
 *
 * @package RuchJow\StatisticsBundle\Command
 */
class SendReminderConfirmationEmailsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('ruchjow:user:sendReminderConfirmationEmails')
            ->setDescription('Send reminder emails to confirm e-mail');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var ObjectManager $entityManager */
        $entityManager = $container->get('doctrine')->getManager();

        /** @var UserRepository $userRepo */
        $userRepo = $entityManager->getRepository('RuchJowUserBundle:User');

        /** @var Mailer $mailer */
        $mailer = $container->get('ruch_jow_user.mailer');

        $reminderTimes = $container->getParameter('ruch_jow_user.reminder.email.remainders');
        $nowDate       = new DateTime('now');


        // Find users
        $qb = $userRepo->createQueryBuilder('u');
        $qb->where($qb->expr()->eq('u.supports', ':supports'))
            ->setParameter('supports', false)
            ->andWhere($qb->expr()->lte('u.nextReminderAt', ':now'))
            ->setParameter('now', $nowDate);

        /** @var User[] $users */
        $users = $qb->getQuery()->getResult();
        $output->writeln('Remainders to be send: ' . count($users));

        // Send remainders
        foreach ($users as $user) {
            $nextReminderCounter = $user->getReminderCounter() + 1;
            if (isset($reminderTimes[$nextReminderCounter]) && $reminderTimes[$nextReminderCounter]) {
                $nextReminder = clone $nowDate;
                $nextReminder->modify('+' . $reminderTimes[$nextReminderCounter] . ' minutes');
            } else {
                $nextReminder = null;
            }

            $mailer->sendReminderEmails($user);
            $user->setReminderCounter($nextReminderCounter)
                ->setNextReminderAt($nextReminder);

            $entityManager->persist($user);
            $entityManager->flush();
            $output->writeln('Mail send to user: ' . $user->getNick());
        }
    }
}