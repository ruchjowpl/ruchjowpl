<?php

namespace RuchJow\UserBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;

use RuchJow\UserBundle\Entity\PreSignedUserData;
use RuchJow\UserBundle\Entity\PreSignedUserDataRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update prices using external service, i.e. Koala SOAP API.
 *
 * @package RuchJow\StatisticsBundle\Command
 */
class ImportPreSignedUsersCommand extends ContainerAwareCommand
{
//    const BATCH_SIZE = 5;

    protected function configure()
    {
        $this->setName('ruchjow:user:importPreSignedUsers')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Import file (pipe separated .psv :) )')
            ->setDescription('Imports nick and email of pre signed users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        /** @var ObjectManager $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        /** @var PreSignedUserDataRepository $preSignedUserDataRepo */
        $preSignedUserDataRepo = $entityManager->getRepository('RuchJowUserBundle:PreSignedUserData');

        // $srcFile   = __DIR__ . '/../src/pre_signed_user_data.psv';
        $srcFile   = $input->getArgument('filePath');

        // Load Supporters
        if (($handler = fopen ($srcFile, 'r')) === false) {
            throw new \Exception('Pre signed user data is unavailable.');
        }


        // Get header.
        fgetcsv($handler, 1000, "|");

//        $i = self::BATCH_SIZE;
        while (($row = fgetcsv($handler, 1000, "|")) !== FALSE)  {

            $error = false;
            $data = $preSignedUserDataRepo->findBy(array('nick' => $row[0]));
            if (count($data)) {
                $output->writeln('Entry with nick "' . $row[0] . '" already exists!');
                $error = true;
            }

            $data = $preSignedUserDataRepo->findBy(array('email' => $row[1]));
            if (count($data)) {
                $output->writeln('Entry with email "' . $row[1] . '" already exists!');
                $error = true;
            }

            if ($error) {
                continue;
            }

            $data = new PreSignedUserData();
            $data
                ->setToken($row[0])
                ->setNick($row[0])
                ->setEmail($row[1]);

            $entityManager->persist($data);

//            if (--$i === 0) {
                $entityManager->flush();
                $entityManager->clear();

//                $i = self::BATCH_SIZE;
//            };
        }
        fclose ($handler);

//        if ($i) {
//            $entityManager->flush();
//        }
    }
}