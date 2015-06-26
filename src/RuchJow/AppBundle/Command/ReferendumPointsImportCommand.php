<?php

namespace RuchJow\AppBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;

use RuchJow\AppBundle\Entity\ReferendumPoint;
use RuchJow\LocalGovBundle\Entity\Support;
use RuchJow\LocalGovBundle\Entity\SupportRepository;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\TerritorialUnitsBundle\Entity\PostCode;
use RuchJow\TerritorialUnitsBundle\Entity\PostCodeRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update prices using external service, i.e. Koala SOAP API.
 *
 * @package RuchJow\StatisticsBundle\Command
 */
class ReferendumPointsImportCommand extends ContainerAwareCommand
{
    const BATCH_SIZE = 5;

    protected function configure()
    {
        $this->setName('ruchjow:referendumPoints:import')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Import file (pipe separated .psv :) )')
            ->addOption('truncate', null, InputOption::VALUE_NONE, 'If set, the task will truncate existing local gov support entries.')
            ->setDescription('Imports referendum points data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        /** @var ObjectManager $entityManager */
        $entityManager = $container->get('doctrine')->getManager();

        // $srcFile   = __DIR__ . '/../src/pre_signed_user_data.psv';
        $srcFile   = $input->getArgument('filePath');

        // Load Referendum points
        if (($handler = fopen ($srcFile, 'r')) === false) {
            throw new \Exception('Referendum points data is unavailable.');
        }


        if ($input->getOption('truncate')) {
            /** @var SupportRepository $repo */
            $repo = $entityManager->getRepository('RuchJowAppBundle:ReferendumPoint');

            $referendumPoints = $repo->findAll();
            foreach ($referendumPoints as $referendumPoint) {
                $entityManager->remove($referendumPoint);
            }
            $entityManager->flush();
            $entityManager->clear();
        }

        // Get header.
        fgetcsv($handler, 1000, "|");

        $i = self::BATCH_SIZE;
        while (($row = fgetcsv($handler, 1000, "|")) !== FALSE)  {

            $echo = '';

            $referendumPoint = new ReferendumPoint();
            $referendumPoint
                ->setTitle($row[0])
                ->setDescription($row[4])
                ->setLat($row[2])
                ->setLng($row[3]);

            if ($commune = $this->findCommune($referendumPoint, $row[1], $entityManager, $echo)) {
                $referendumPoint->setCommune($commune);
            }

            if ($echo) {
                $output->writeln($referendumPoint->getTitle());
                $output->writeln($echo);
            }

            $entityManager->persist($referendumPoint);

            if (--$i === 0) {
                $entityManager->flush();
                $entityManager->clear();

                sleep(1);

                $i = self::BATCH_SIZE;
            };
        }
        fclose ($handler);

        if ($i) {
            $entityManager->flush();
        }
    }

    /**
     * @param ReferendumPoint $point
     * @param string          $name
     * @param ObjectManager   $manager
     * @param                 $echo
     *
     * @return null|Commune
     */
    function findCommune(ReferendumPoint $point, $name, ObjectManager $manager, &$echo)
    {
        $communeRepo = $manager->getRepository('RuchJowTerritorialUnitsBundle:Commune');

        $communes = $communeRepo->findBy(array('name' => $name));
        if (count($communes) === 1) {
            return $communes[0];
        }
        $echo .= "Multiple communes found. \n";

        if (preg_match('/(\d\d)-(\d\d\d)/', $point->getDescription(), $matches)) {

            /** @var PostCodeRepository $repo */
            $repo = $manager->getRepository('RuchJowTerritorialUnitsBundle:PostCode');

            /** @var PostCode $postCode */
            $postCode = $repo->find((int) ($matches[1] . $matches[2]));
            if ($postCode) {
                $pcCommunes = $postCode->getCommunes();
                if ($pcCommunes->count() === 1) {
                    $echo .= "Commune chosen by post-code. \n";

                    return $pcCommunes->first();
                }

                $echo .= "Multiple communes found by post-code. \n";
            }
        }

        if ($communes) {
            $echo .= "Commune chosen by name. \n";

            return $communes[0];
        }

        if (isset($pcCommunes) && $pcCommunes->count() > 0) {
            $echo .= "Commune chosen by post-code. \n";

            return $pcCommunes->first();
        }

        $echo .= "Commune not found! \n";

        return null;
    }
}