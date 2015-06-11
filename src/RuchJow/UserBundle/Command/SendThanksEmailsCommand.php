<?php

namespace RuchJow\UserBundle\Command;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use RuchJow\UserBundle\Entity\UserManager;
use RuchJow\UserBundle\Services\Mailer;
use RuchJow\TransferujPlBundle\Entity\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends e-mail with thanks for support
 *
 * @package RuchJow\StatisticsBundle\Command
 */
class SendThanksEmailsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('ruchjow:user:sendThanksEmails')
            ->setDescription('Send emails with thanks for payment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $container = $this->getContainer();

        /** @var ObjectManager $entityManager */
        $entityManager = $container->get('doctrine')->getManager();

        /** @var PaymentRepository $paymentRepo */
        $paymentRepo = $entityManager->getRepository('RuchJowTransferujPlBundle:Payment');

        /** @var UserManager $userManager */
        $userManager = $container->get('ruch_jow_user.user_manager');

        /** @var Mailer $mailer */
        $mailer = $container->get('ruch_jow_user.mailer');

        $time = $container->getParameter('ruch_jow_user.thanks.email.time');
        $time = (new \DateTime('now'))->sub(new \DateInterval('PT' . $time . 'S'));
        $payments = $paymentRepo->findAllPayDone(false, $time);

        foreach ($payments as $payment) {
            $user  = $userManager->findUserByEmail($payment->getPayersEmail());

            $mailer->sendThanksEmails($user);

            $payment->setIsSentEmail(true);
            $entityManager->persist($payment);
            $entityManager->flush();
        }
    }
}