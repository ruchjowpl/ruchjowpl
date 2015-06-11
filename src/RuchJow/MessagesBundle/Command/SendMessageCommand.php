<?php

namespace RuchJow\MessagesBundle\Command;

use RuchJow\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends e-mail with thanks for support
 *
 * @package RuchJow\StatisticsBundle\Command
 */
class SendMessageCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('ruchjow:messages:send')
            ->addArgument('sender', InputArgument::REQUIRED, 'Message sender')
            ->addArgument('recipient', InputArgument::REQUIRED, 'Message recipient')
            ->addArgument('subject', InputArgument::REQUIRED, 'Message subject')
            ->addArgument('body', InputArgument::REQUIRED, 'Message body')
            ->setDescription('Send internal message');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $container = $this->getContainer();

        $manager = $container->get('ruch_jow_messages.manager');
        $userManager = $container->get('ruch_jow_user.user_manager');

        /** @var User $sender */
        $sender = $userManager->findUserByUsername($input->getArgument('sender'));
        if (!$sender) {
            $output->writeln('<error>Sender user not found!</error>');
            return;
        }

        /** @var User $recipient */
        $recipient = $userManager->findUserByUsername($input->getArgument('recipient'));
        if (!$recipient) {
            $output->writeln('<error>Recipient user not found!</error>');
            return;
        }

        $subject = $input->getArgument('subject');
        if (!$subject) {
            $output->writeln('<error>Subject must not be empty!</error>');
            return;
        }

        $body = $input->getArgument('body');
        if (!$body) {
            $output->writeln('<error>Body must not be empty!</error>');
            return;
        }

        $manager->sendMessage($sender, $recipient, $subject, $body);

        $output->writeln('Message sent.');
    }

}