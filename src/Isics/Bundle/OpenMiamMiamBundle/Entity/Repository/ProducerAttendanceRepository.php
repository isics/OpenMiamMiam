<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;

class ProducerAttendanceRepository extends EntityRepository
{
    /**
     * Find producer attendances for several branch occurrences
     *
     * @param array $branchOccurrences
     *
     * @return array
     */
    public function findForBranchOccurrences($branchOccurrences)
    {
        $branchOccurrencesIds = array();

        foreach ($branchOccurrences as $branchOccurrence) {
            $branchOccurrencesIds[] = $branchOccurrence->getId();
        }

        /*
        return $this->createQuery('SELECT pa
                FROM IsicsOpenMiamMiamBundle:ProducerAttendance
                WHERE pa.branchOccurrence')
            ->getResult();*/

        $qb = $this->createQueryBuilder('pa');
        $qb ->innerJoin('pa.branchOccurrence', 'bo')
            ->add('where', $qb->expr()->in('bo.id', ':branchOccurrencesIds'))
            ->setParameter('branchOccurrencesIds', $branchOccurrencesIds);
        return $qb->getQuery()->getResult();
    }
} 