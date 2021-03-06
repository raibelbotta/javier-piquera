<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Reserva;

/**
 * ReservaLogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReservaLogRepository extends EntityRepository
{
    public function findByReservaOrderedByCreatedAt(Reserva $reserva)
    {
        $manager = $this->getEntityManager();
        $query = $manager->createQuery('SELECT rl FROM AppBundle:ReservaLog rl JOIN rl.reserva r WHERE r.id = :id ORDER BY rl.createdAt')
                ->setParameter('id', $reserva->getId())
                ;

        return $query->getResult();
    }
}
