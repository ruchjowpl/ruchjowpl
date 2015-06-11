<?php

namespace RuchJow\TerritorialUnitsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use RuchJow\TerritorialUnitsBundle\Entity\CommuneRepository;
use RuchJow\TerritorialUnitsBundle\Entity\PostCode;
use RuchJow\TerritorialUnitsBundle\Entity\PostCodeRepository;

class LoadPostCodesData extends AbstractFixture implements OrderedFixtureInterface
{
    const BATCH_SIZE = 1000;

    /**
     * {@inheritDoc}
     */
    function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $srcFile   = __DIR__ . '/../src/postcodes.psv';

        $postCodesCache = array();

        if (($handler = fopen ($srcFile, 'r')) === false) {
            throw new \Exception('Post codes are unavailable.');
        }

        /** @var CommuneRepository $communeRepo */
        $communeRepo = $manager->getRepository('RuchJowTerritorialUnitsBundle:Commune');
        /** @var PostCodeRepository $postCodeRepo */
        $postCodeRepo = $manager->getRepository('RuchJowTerritorialUnitsBundle:PostCode');

        // Get header.
        fgetcsv($handler, 1000, "|");

        $i = 0;
        while (($row = fgetcsv($handler, 1000, "|")) !== FALSE)  {
            $i++;
//            echo "$i, ";
            if ($i % self::BATCH_SIZE == 0) {
                $manager->flush();
                $manager->clear();
                $postCodesCache = array();
                gc_collect_cycles();
                echo 'Lines processed: ' . $i . "\n";
                echo 'Memory usage: ' . ((int) (memory_get_usage() / 1024 / 1024)) . "MB\n";
            }

            $communes = $communeRepo->getByCommuneDistrictRegion($row[1], $row[2], $row[3]);

            if (empty($communes)) {
                echo 'Commune not found for: ' . $row[0] . '|' . $row[1] . '|' . $row[2] . '|' . $row[3] . "\n";
                continue;
            }

            $commune = null;
            if (count($communes) > 1) {

                $type = mb_substr($row[4], 0, mb_strlen($row[1])) === $row[1] ?
                    'gmina miejska' :
                    'gmina wiejska';


                foreach ($communes as $c) {
                    if ($c->getType() === $type) {
                        $commune = $c;
                    }
                }
                if (!$commune) {
                    echo 'More than one commune found for: ' . $row[0] . '|' . $row[1] . '|' . $row[2] . '|' . $row[3] . "\n";
                    foreach ($communes as $commune) {
                        echo $commune->getId() . ', ';
                    }
                    echo "\n";
                }/* else {
                    echo 'More than one commune found for: ' . $row[0] . '|' . $row[1] . '|' . $row[2] . '|' . $row[3] . "\n";
                    echo 'Commune of type ' . $type . " has been chosen.\n";
                }*/

                continue;
            } else {
                $commune = $communes[0];
            }

            if (isset($postCodesCache[$row[0]])) {
                $postCode = $postCodesCache[$row[0]];
            } else {
                $postCode = $postCodeRepo->findOneByCode($row[0]);
            }


            if ($postCode) {
                $found = false;
                foreach ($postCode->getCommunes() as $c) {
                    if ($c->getId() == $commune->getId()) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    continue;
                }
            } else {
                $postCode = new PostCode();
                $postCode->setCode($row[0]);
            }

            $postCode->addCommune($commune);
            $manager->persist($postCode);
            $postCodesCache[$row[0]] = $postCode;
        }
        fclose ($handler);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}