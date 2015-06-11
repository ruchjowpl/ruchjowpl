<?php

namespace RuchJow\TerritorialUnitsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\TerritorialUnitsBundle\Entity\District;
use RuchJow\TerritorialUnitsBundle\Entity\Region;

class LoadTerritorialUnitsData extends AbstractFixture implements OrderedFixtureInterface
{

    const COMMUNES_BATCH_SIZE = 10;

    /**
     * {@inheritDoc}
     */
    function load(ObjectManager $manager)
    {
        $regionsSrcFile   = __DIR__ . '/../src/regions.psv';
        $districtsSrcFile = __DIR__ . '/../src/districts.psv';
        $communesSrcFile = __DIR__ . '/../src/communes.psv';

        $regions   = array();
        $districts = array();
        $communes  = array();

        // Load Regions
        if (($handler = fopen ($regionsSrcFile, 'r')) === false) {
            throw new \Exception('Regions are unavailable.');
        }

        // Get header.
        fgetcsv($handler, 1000, "|");

        while (($row = fgetcsv($handler, 1000, "|")) !== FALSE)  {

            $region = new Region();
            $region->setName($row[1]);

            $manager->persist($region);
            $regions[$row[0]] = $region;
        }
        fclose ($handler);
        $manager->flush();

        // Load Districts
        if (($handler = fopen ($districtsSrcFile, 'r')) === false) {
            throw new \Exception('Districts are unavailable.');
        }

        // Get header.
        fgetcsv($handler, 1000, "|");

        while (($row = fgetcsv($handler, 1000, "|")) !== FALSE)  {

            $district = new District();
            $district
                ->setName($row[1])
                ->setRegion($regions[$row[2]]);
            $manager->persist($district);

            $districts[$row[0]] = $district;
        }
        fclose ($handler);
        $manager->flush();

        // Load Communes
        if (($handler = fopen ($communesSrcFile, 'r')) === false) {
            throw new \Exception('Communes are unavailable.');
        }

        // Get header.
        fgetcsv($handler, 1000, "|");

        $i = 0;
        while (($row = fgetcsv($handler, 1000, "|")) !== FALSE)  {

            $commune = new Commune();
            $commune
                ->setName($row[1])
                ->setType($row[6])
                ->setTerytId($row[0])
                ->setDistrict($districts[$row[4]]);
            $manager->persist($commune);
            $communes[] = $commune;
            $i++;

            if ($i >= self::COMMUNES_BATCH_SIZE) {
                $manager->flush();

                $i = 0;
                foreach ($communes as $commune) {
                    $manager->detach($commune);
                }
                $communes = array();
            }
        }
        fclose ($handler);
        $manager->flush();
    }


    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}