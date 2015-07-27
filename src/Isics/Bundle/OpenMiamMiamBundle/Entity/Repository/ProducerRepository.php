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
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;

class ProducerRepository extends EntityRepository
{
    /**
     * Finds all producers for a branch
     *
     * @param Branch  $branch Branch
     * @param integer $limit  Limit
     *
     * @return array
     */
    public function findAllRandomForBranch(Branch $branch, $limit = 5)
    {
        // Retrieve all producers ids
        $ids = $this->findAllIds($branch);
        if (empty($ids)) {
            return array();
        }

        // Randomize ids
        shuffle($ids);

        // Truncate
        array_splice($ids, $limit);

        // Retrieve producers
        $producers = $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        // Randomize producers
        shuffle($producers);

        return $producers;
    }

    /**
     *
     * @param Branch $branch
     * @param boolean $randomize
     *
     * @return array
     */
    public function findAllProducer(Branch $branch, $randomize = true)
    {
        // Retrieve all producers ids
        $ids = $this->findAllIds($branch);
        if (empty($ids)) {
            return array();
        }

        // Retrieve producers
        $producers = $this->createQueryBuilder('p')
        ->where('p.id IN (:ids)')
        ->setParameter('ids', $ids)
        ->orderBy('p.name')
        ->getQuery()
        ->getResult();

        // Randomize producers ?
        if ((bool)$randomize) {
            shuffle($producers);
        }

        return $producers;
    }


    /**
     * Finds all ids
     *
     * @param Branch $branch Branch
     *
     * @return array
     */
    public function findAllIds(Branch $branch = null)
    {
        $qb = $this->createQueryBuilder('p')->select('p.id')
            ->where('p.deletedAt is null');
        if (null !== $branch) {
            $qb
                ->innerJoin('p.associationHasProducer', 'ahp')
                ->innerJoin('ahp.branches', 'b')
                ->andWhere('b.id = :branchId')
                ->setParameter('branchId', $branch->getId());
        }

        $flattenIds = array();
        foreach ($qb->getQuery()->getResult() as $id) {
            $flattenIds[] = $id['id'];
        }

        return $flattenIds;
    }

    /**
     * Finds ids of producers in branches
     *
     * @param mixed $branch Branch or array of branches
     *
     * @return array
     */
    public function findIdsForBranch($branch)
    {
        $flattenIds = array();
        foreach ($this->getIdsForBranchQueryBuilder($branch)->getQuery()->getResult() as $id) {
            $flattenIds[] = $id['id'];
        }

        return $flattenIds;
    }

    /**
     * Finds ids of producers without branch
     *
     * @return array
     */
    public function findIdsWithoutBranch()
    {
        $flattenIds = array();
        foreach ($this->getIdsWithoutBranchQueryBuilder()->getQuery()->getResult() as $id) {
            $flattenIds[] = $id['id'];
        }

        return $flattenIds;
    }

    /**
     * Filter for branch
     *
     * @param mixed        $branch Branch or array of branches
     * @param QueryBuilder $qb     Query builder
     *
     * @return QueryBuilder
     */
    public function filterBranch($branch, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        $qb->andWhere('p.deletedAt IS NULL');

        if ($branch instanceof \ArrayAccess) {
            $branchesIds = array();
            foreach ($branch as $_branch) {
                $branchesIds[] = $_branch->getId();
            }

            $qb
                ->innerJoin('p.associationHasProducer', 'ahp')
                ->innerJoin('ahp.branches', 'b')
                ->andWhere('b.id IN (:branchesIds)')
                ->setParameter('branchesIds', $branchesIds);

        } else {
            $qb
                ->innerJoin('p.associationHasProducer', 'ahp')
                ->innerJoin('ahp.branches', 'b', Expr\Join::WITH, $qb->expr()->eq('b', ':branch'))
                ->setParameter('branch', $branch);
        }

        return $qb;
    }

    /**
     * Filter without branch
     *
     * @param QueryBuilder $qb Query builder
     *
     * @return QueryBuilder
     */
    public function filterWithoutBranch(QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        $qb
            ->innerJoin('p.associationHasProducer', 'ahp')
            ->leftJoin('ahp.branches', 'b')
            ->andWhere('b.id IS NULL');

        return $qb;
    }

    /**
     * Return producers who are attendees or not
     *
     * @param QueryBuilder $qb
     * @param null $isAttendee
     *
     * @return QueryBuilder
     */
    public function filterAttendances(BranchOccurrence $branchOccurrence, $isAttendee, QueryBuilder $qb = null)
    {
        $qb = ($qb === null ? $this->createQueryBuilder('p') : $qb);

        $qb
            ->leftJoin('p.producerAttendances', 'pa')
            ->andWhere('pa.branchOccurrence = :branchOccurrence')
            ->setParameter('branchOccurrence', $branchOccurrence)
            ->andWhere('pa.isAttendee = :isAttendee')
            ->setParameter('isAttendee', $isAttendee)
            ->andWhere('p.deletedAt IS NULL');

        return $qb;
    }

    /**
     * Returns query builder to find all producers ids of branches
     *
     * @param mixed $branch Branch or array of branches
     *
     * @return QueryBuilder
     */
    public function getIdsForBranchQueryBuilder($branch)
    {
        return $this->filterBranch($branch)
            ->select('DISTINCT p.id')
            ->andWhere('p.deletedAt is null');
    }

    /**
     * Returns query builder to find all producers without branch
     *
     * @return QueryBuilder
     */
    public function getIdsWithoutBranchQueryBuilder()
    {
        return $this->filterWithoutBranch()
            ->select('p.id');
    }

    /**
     * Returns query builder to find all producer of association
     *
     * @param Association  $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForAssociationQueryBuilder(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->innerjoin('p.associationHasProducer', 'ahp')
                ->innerjoin('ahp.association', 'a')
                ->andWhere('a.id = :associationId')
                ->setParameter('associationId', $association->getId())
                ->addOrderBy('p.name', 'ASC');
    }

    /**
     * Return query builder to find all sales order row
     *
     * @return QueryBuilder
     */
    public function getForTransferExportQueryBuilder()
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->innerJoin('p.salesOrderRows', 'sor')
            ->innerJoin('sor.salesOrder', 'so')
            ->innerJoin('so.branchOccurrence', 'bo');
    }
}
