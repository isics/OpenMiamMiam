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

use Doctrine\ORM\QueryBuilder;
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
                ->addOrderBy('so.id')
                ->addOrderBy('sor.name', 'ASC');

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
        return $this->createQueryBuilder('so')
                ->addSelect('sor')
                ->leftJoin('so.salesOrderRows', 'sor')
                ->andWhere('so.branchOccurrence = :branchOccurrence')
                ->setParameter('branchOccurrence', $branchOccurrence)
                ->addOrderBy('so.id')
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns sales orders for a branch occurrence excluding anonymous
     *
     * @param BranchOccurrence $branchOccurrence
     *
     * @return array
     */
    public function findForBranchOccurrenceExcludingAnonymous(BranchOccurrence $branchOccurrence)
    {
        return $this->createQueryBuilder('so')
            ->where('so.user IS NOT NULL')
            ->andWhere('so.branchOccurrence = :branchOccurrence')
            ->setParameter('branchOccurrence', $branchOccurrence)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns credit for user and association
     *
     * @param Association $association
     * @param User $user
     *
     * @return float
     */
    public function getTotalForUserAndAssociation(Association $association, User $user = null)
    {
         $qb = $this->createQueryBuilder('so')
                ->select('SUM(so.total) AS totalSum')
                ->innerJoin('so.branchOccurrence', 'bo')
                ->innerJoin('bo.branch', 'b')
                ->andWhere('b.association = :association')
                ->setParameter('association', $association);

        if (null === $user) {
            $qb->andWhere($qb->expr()->isNull('so.user'));
        } else {
            $qb->andWhere('so.user = :user')
                ->setParameter('user', $user);
        }

        $result = $qb->getQuery()->getSingleResult();

        return $result['totalSum'];
    }

    /**
     * Returns all sales order not settled for user and association
     *
     * @param User $user
     * @param Association $association
     *
     * @return array
     */
    public function findNotSettledForUserAndAssociation(User $user, Association $association)
    {
        return $this->createQueryBuilder('so')
                ->innerJoin('so.branchOccurrence', 'bo')
                ->innerJoin('bo.branch', 'b')
                ->andWhere('so.credit < 0')
                ->andWhere('so.user = :user')
                ->andWhere('b.association = :association')
                ->addOrderBy('so.id', 'ASC')
                ->setParameter('user', $user)
                ->setParameter('association', $association)
                ->getQuery()
                ->getResult();
    }


    /**
     * Gets query builder to get sales orders with rows
     *
     * @return QueryBuilder
     */
    public function getWithRowsQueryBuilder()
    {
        return $this->createQueryBuilder('so')
                ->addSelect('sor, p')
                ->leftJoin('so.salesOrderRows', 'sor')
                ->leftJoin('sor.producer', 'p')
                ->addOrderBy('so.lastname')
                ->addOrderBy('so.firstname')
                ->addOrderBy('p.name')
                ->addOrderBy('sor.name');
    }

    /**
     * Finds a sales order with rows specifically ordered
     *
     * @param $id Sales order ID
     *
     * @return SalesOrder|null
     */
    public function findOneWithRows($id)
    {
        return $this->getWithRowsQueryBuilder()
                ->where('so.id = :salesOrderId')
                ->setParameter('salesOrderId', $id)
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     * Returns sales orders with rows for a branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     *
     * @return array
     */
    public function findWithRowsForBranchOccurrence(BranchOccurrence $branchOccurrence)
    {
        return $this->filterBranchOccurrence($this->getWithRowsQueryBuilder(), $branchOccurrence)
                ->getQuery()
                ->getResult();
    }

    /**
     * Filters sales orders by branch occurrence
     *
     * @param QueryBuilder $qb
     * @param BranchOccurrence $branchOccurrence
     *
     * @return QueryBuilder
     */
    public function filterBranchOccurrence(QueryBuilder $qb, BranchOccurrence $branchOccurrence)
    {
        return $qb->andWhere('so.branchOccurrence = :branchOccurrence')
                ->setParameter('branchOccurrence', $branchOccurrence);
    }

    /**
     * Returns query builder to find all sales order of user
     *
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function getForUserQueryBuilder($user)
    {
        return $this->filterUser($user)
            ->addOrderBy('bo.begin', 'DESC')
            ->addOrderBy('so.date', 'DESC');
    }

    /**
     * Returns all sales order of user
     *
     * @param User $user
     *
     * @return array SalesOrder
     */
    public function findForUser($user)
    {
        return $this->getForUserQueryBuilder($user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters all sales order of user
     *
     * @param User          $user
     * @param QueryBuilder  $qb
     *
     * @return QueryBuilder
     */
    public function filterUser($user, QueryBuilder $qb = null)
    {
       $qb = null === $qb ? $this->createQueryBuilder('so') : $qb;

       return $qb->join('so.branchOccurrence', 'bo')
            ->join('bo.branch', 'b')
            ->where('so.user = :user')
            ->setParameter('user', $user);
    }

    /**
     * @param Association $association
     * @param User        $user
     *
     * @return bool
     */
    public function hasSalesOrdersNotSettledForAssociation(Association $association, User $user = null)
    {
        $qb = $this->createQueryBuilder('so')
            ->select('COUNT(so.id) AS counter')
            ->innerJoin('so.branchOccurrence', 'bo')
            ->innerJoin('bo.branch', 'b')
            ->andWhere('so.credit < 0')
            ->andWhere('b.association = :association')
            ->setParameter('association', $association);

        if (null !== $user) {
            $qb->andWhere('so.user = :user')->setParameter('user', $user);
        }
        else {
            $qb->andWhere('so.user IS NULL');
        }

        $result = $qb->getQuery()->getSingleResult();

        return (bool)$result['counter'];
    }
}
