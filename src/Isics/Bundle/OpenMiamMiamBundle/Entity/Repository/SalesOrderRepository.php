<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class SalesOrderRepository extends EntityRepository
{
    /**
     * Returns true if ref of sales order is unique
     *
     * @param SalesOrder $order
     *
     * @return boolean
     */
    public function isRefUnique(SalesOrder $order)
    {
        $qb = $this->createQueryBuilder('so')
                ->select('COUNT(so.id) AS counter')
                ->innerJoin('so.branchOccurrence', 'bo')
                ->innerJoin('bo.branch', 'b')
                ->andWhere('so.ref = :ref')
                ->setParameter('ref', $order->getRef())
                ->andWhere('b.association = :association')
                ->setParameter('association', $order->getBranchOccurrence()->getBranch()->getAssociation());

        if (null !== $order->getId()) {
            $qb->andWhere('so.id != :id')->setParameter('id', $order->getId());
        }

        $result = $qb->getQuery()->getSingleResult();

        return $result['counter'] == 0;
    }

    /**
     * Returns sales orders for a producer (concerned by at least one row)
     *
     * @param Producer $producer
     * @param BranchOccurrence $branchOccurrence
     *
     * @return array
     */
    public function findForProducer(Producer $producer, BranchOccurrence $branchOccurrence = null)
    {
        $qb = $this->createQueryBuilder('so')
                ->addSelect('bo, sor')
                ->innerJoin('so.branchOccurrence', 'bo')
                ->innerJoin('so.salesOrderRows', 'sor')
                ->andWhere('sor.producer = :producer')
                ->setParameter('producer', $producer)
                ->addOrderBy('so.id');

        if (null !== $branchOccurrence) {
            $qb->andWhere('so.branchOccurrence = :branchOccurrence')
                ->setParameter('branchOccurrence', $branchOccurrence);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns sales orders for a branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     *
     * @return array
     */
    public function findForBranchOccurrence(BranchOccurrence $branchOccurrence)
    {
        $qb = $this->createQueryBuilder('so')
                ->addSelect('sor')
                ->leftJoin('so.salesOrderRows', 'sor')
                ->andWhere('so.branchOccurrence = :branchOccurrence')
                ->setParameter('branchOccurrence', $branchOccurrence)
                ->addOrderBy('so.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns credit for user and association
     *
     * @param User $user
     * @param Association $association
     *
     * @return float
     */
    public function getTotalForUserAndAssociation(User $user, Association $association)
    {
        $result = $this->createQueryBuilder('so')
                ->select('SUM(so.total) AS totalSum')
                ->innerJoin('so.branchOccurrence', 'bo')
                ->innerJoin('bo.branch', 'b')
                ->andWhere('b.association = :association')
                ->setParameter('association', $association)
                ->andWhere('so.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleResult();

        return $result['totalSum'];
    }
}
