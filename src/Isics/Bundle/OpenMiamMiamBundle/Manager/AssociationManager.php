<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;


use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Model\Association\ProducerTransfer;

class AssociationManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs object
     *
     * @param EntityManager   $entityManager
     * @param ActivityManager $activityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager   = $entityManager;
    }

    public function getProducerTransferForMonth(Association $association, \DateTime $fromDate)
    {
        $toDate = clone $fromDate;
        $toDate->modify('first day of next month midnight - 1 second');

        // Retrieve branch occurrence to consider
        $branchOccurrences = $this->entityManager
            ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
            ->findForAssociationByDateRange($association, $fromDate, $toDate);

        // Get ids from records
        $branchOccurrenceIds = array();

        foreach ($branchOccurrences as $branchOccurrence) {
            $branchOccurrenceIds[] = $branchOccurrence->getId();
        }

        $producersDataQueryBuilder = $this->entityManager->createQueryBuilder();
        $producersData = $producersDataQueryBuilder
            ->select('p.id AS producer_id')
            ->addSelect('bo1.id AS branch_occurrence_id')
//            ->addSelect('COALESCE(b1.name, b2.name) AS branch_name')
//            ->addSelect('COALESCE(bo1.id, bo2.id) AS branch_occurrence_id')
//            ->addSelect('COALESCE(bo1.end, bo2.end) AS branch_occurrence_date')
            ->addSelect('SUM(sor.total) AS amount')
            ->from('IsicsOpenMiamMiamBundle:Producer', 'p')
            ->leftJoin('p.salesOrderRows', 'sor')
            ->leftJoin('sor.salesOrder', 'so')
            ->leftJoin('so.branchOccurrence', 'bo1')
            ->leftJoin('bo1.branch', 'b1')
//            ->leftJoin('p.producerAttendances', 'pa')
//            ->leftJoin('pa.branchOccurrence', 'bo2')
//            ->leftJoin('bo2.branch', 'b2')
            ->where(//$producersDataQueryBuilder->expr()->orX(
                $producersDataQueryBuilder->expr()->in('bo1.id', $branchOccurrenceIds)/*,
                $producersDataQueryBuilder->expr()->andX(
                    $producersDataQueryBuilder->expr()->in('bo2.id', $branchOccurrenceIds),
                    $producersDataQueryBuilder->expr()->eq('pa.isAttendee', $producersDataQueryBuilder->expr()->literal(true))
                )
            )*/)
            ->addGroupBy('bo1.id')
            ->addGroupBy('p.id')
            ->getQuery()
            ->getResult();

        $producerIds = array();

        foreach ($producersData as $producersDatum) {
            $producerIds[] = $producersDatum['producer_id'];
        }

        $producerIds = array_unique($producerIds);

        $producersQueryBuilder = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Producer')
            ->createQueryBuilder('p');
        $producers = $producersQueryBuilder
            ->where($producersQueryBuilder->expr()->in('p.id', $producerIds))
            ->orderBy('p.name')
            ->getQuery()
            ->getResult();

        return new ProducerTransfer($branchOccurrences, $producers, $producersData);
    }
} 