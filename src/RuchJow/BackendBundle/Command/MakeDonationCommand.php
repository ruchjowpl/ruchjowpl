<?php

namespace RuchJow\BackendBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;

use Ruchjow\TransferujPlBundle\Service\PaymentManager;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates donation payment.
 *
 * @package RuchJow\BackendBundle\Command
 */
class MakeDonationCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('backend:admin:makeDonation')
            ->addArgument('amount', InputArgument::REQUIRED, 'Payment amount')
            ->addArgument('username', InputArgument::REQUIRED, 'User name')
            ->setDescription('Creates donation payment.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        // $srcFile   = __DIR__ . '/../src/pre_signed_user_data.psv';
        $amount = $input->getArgument('amount');
        $username = $input->getArgument('username');

        /** @var UserManager $userManager */
        $userManager = $container->get('fos_user.user_manager');
        /** @var PaymentManager $paymentManager */
        $paymentManager = $container->get('ruch_jow_transferuj_pl.payment_manager');

        /** @var User $user */
        $user = $userManager->findUserByUsername($username);

        if (!$user) {
            $output->writeln('User not found');

            return 1;
        }

        $now = new \DateTime();
        $transactionId = 'MANUAL-DONATION-' . $now->format('Y-m-d-H-i-s') . '-' . rand();
        $paymentManager->persistPayment(
            $transactionId,
            array(
                'date' => $now,
                'crc'  => json_encode(array(
                    'type' => 'donation',
                    'user' => $user->getId(),
                )),
                'amount' => floatval($amount),
                'paid' => floatval($amount),
                'description' => 'Wpłata na rzecz Ruchu JOW',
                'status' => 'TRUE',
                'error'  => 0,
                'payersEmail' => $user->getEmailCanonical(),
            )
        );

        $output->writeln('Donation persisted.');
    }
}