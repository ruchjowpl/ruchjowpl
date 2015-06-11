<?php

namespace RuchJow\LocalGovBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;

use RuchJow\LocalGovBundle\Entity\Support;
use RuchJow\LocalGovBundle\Entity\SupportRepository;
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
class ImportLocalGovCommand extends ContainerAwareCommand
{
    const BATCH_SIZE = 5;

    protected function configure()
    {
        $this->setName('ruchjow:localGov:import')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Import file (pipe separated .psv :) )')
            ->addOption('truncate', null, InputOption::VALUE_NONE, 'If set, the task will truncate existing local gov support entries.')
            ->setDescription('Imports local gov support data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        /** @var ObjectManager $entityManager */
        $entityManager = $container->get('doctrine')->getManager();

        // $srcFile   = __DIR__ . '/../src/pre_signed_user_data.psv';
        $srcFile   = $input->getArgument('filePath');

        // Load Supporters
        if (($handler = fopen ($srcFile, 'r')) === false) {
            throw new \Exception('Local gov supporters data is unavailable.');
        }


        if ($input->getOption('truncate')) {
            /** @var SupportRepository $repo */
            $repo = $entityManager->getRepository('RuchJowLocalGovBundle:Support');

            $supports = $repo->findAll();
            foreach ($supports as $support) {
                $entityManager->remove($support);
            }
            $entityManager->flush();
            $entityManager->clear();
        }

        // Get header.
        fgetcsv($handler, 1000, "|");

        $i = self::BATCH_SIZE;
        while (($row = fgetcsv($handler, 1000, "|")) !== FALSE)  {

            $echo = '';

            $support = new Support();
            $support
                ->setName($row[0])
                ->setType($row[1])
                ->setTitle($row[2])
                ->setSubtitle($row[3])
                ->setDescription($row[4])
                ->setLink($row[5] ? $row[5] : null)
                ->setLinkTitle($row[6] ? $row[6] : null)
                ->setSearchString($row[7]);

            $this->updateSupport($support, $entityManager, $echo);

            if ($echo) {
                $output->writeln($support->getName());
                $output->writeln($echo);
            }

            $entityManager->persist($support);

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
     * @param Support       $support
     * @param ObjectManager $manager
     * @param               $echo
     */
    function updateSupport(Support $support, ObjectManager $manager, &$echo)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        $params = http_build_query(array(
            'address' => $support->getSearchString(),
        ));

        //  Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        // Execute
        $result=curl_exec($ch);
        // Closing
        curl_close($ch);


        $result = json_decode($result, true);
        $support->setSearchResult($result);

        if (
            ! $result
            || !isset($result['results'])
            || !isset($result['results'][0])
        ) {
            $echo .= "Geocoding failed: no results!\n";
            $echo .= $url . '?' . $params . "\n";

            return;
        }

        if (count($result['results']) > 1) {
            $echo .= "Geocoding warning: multiple results!\n";
        }

        $data = $result['results'][0];
        $support
            ->setAddress($data['formatted_address'])
            ->setLat($data['geometry']['location']['lat'])
            ->setLng($data['geometry']['location']['lng']);

        if (preg_match('/(\d\d)-(\d\d\d)/', $support->getSearchString(), $matches)) {
            $code = $matches[1] . '-' . $matches[2];
            $support->setPostCode($code);

            /** @var PostCodeRepository $repo */
            $repo = $manager->getRepository('RuchJowTerritorialUnitsBundle:PostCode');

            /** @var PostCode $postCode */
            $postCode = $repo->find((int) ($matches[1] . $matches[2]));
            if (!$postCode) {
                $echo .= "Geocoding warning: postal code not found ($code)!\n";
            } else {
                $communes = $postCode->getCommunes();
                if (!empty($communes)) {
                    $commune = $communes->first();

                    $support->setCommune($commune);
                }
            }
        } else {
            $echo .= "Geocoding warning: postal code not found!\n";
        }


        if ($echo) {
            $echo .= $url . '?' . $params . "\n";
        }
    }
}