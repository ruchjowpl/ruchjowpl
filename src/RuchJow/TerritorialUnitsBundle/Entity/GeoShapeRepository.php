<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class GeoShapeRepository extends EntityRepository
{
    /**
     * Retrieves a shape of given type, related to a corresponding territorial unit
     *
     * @param string $type
     * @param int $id required, except for type 'country'
     *
     * @return GeoShape
     *
     * @throws \RuntimeException
     */
    public function findOneByTypeAndId($type, $id) {
        if (!preg_match('/country|region|district|commune/', $type)) {
            throw new \RuntimeException('Unknown shape type!');
        }

        if ($type == 'country') {
            return $this->findOneByType($type);
        }

        if (!$id) {
            throw new \RuntimeException('No id provided!');
        }

        return $this->createQueryBuilder('gs')
            ->leftJoin('gs.' . $type, 'o')
            ->where('gs.type = :type')
            ->andWhere('o.id = :o_id')
            ->getQuery()
            ->setParameters(array('type' => $type, 'o_id' => (int) $id))
            ->getSingleResult(); // throws an exception if 0 or > 1 results found
    }
}