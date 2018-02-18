<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ReservaTercero;

/**
 * ReservaTerceroRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReservaTerceroRepository extends \Doctrine\ORM\EntityRepository
{
    public function countByType($type)
    {
        $query = $this->createQueryBuilder('r')
            ->select('COUNT(r) AS cantidad')
            ->where('r.type = :type AND r.state <> :stateCanceled')
            ->setParameters(array(
                'type' => $type,
                'stateCanceled' => ReservaTercero::STATE_CANCELLED
            ))
            ->getQuery();

        $result = $query->getResult();

        return $result[0]['cantidad'];
    }
}
