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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;

class BranchOccurrenceRepository extends EntityRepository
{
    /**
     * Return branch occurrences between $fromDate and $toDate for association $association
     *
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\Association $association
     * @param \DateTime                                           $fromDate
     * @param \DateTime                                           $toDate
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function findForAssociationByDateRange(Association $association, \DateTime $fromDate, \DateTime $toDate)
    {
        if ($fromDate > $toDate) {
            throw new \InvalidArgumentException('$fromDate must be smaller than $toDate.');
        }

        return $this->createQueryBuilder('bo')
            ->innerJoin('bo.branch', 'b')
            ->where('bo.end BETWEEN :from AND :to')
            ->andWhere('b.association = :association')
            ->setParameter('from', $fromDate)
            ->setParameter('to', $toDate)
            ->setParameter('association', $association)
            ->orderBy('bo.end')
            ->getQuery()
            ->getResult();
    }
    /**
     * Finds next occurrence for a branch which is not closed
     *
     * @param Branch $branch Branch
     *
     * @return BranchOccurrence|null
     */
    public function findOneNextNotClosedForBranch(Branch $branch)
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval(
            sprintf('PT%sS', $branch->getAssociation()->getOpeningDelay())
        ));

        return $this->createQueryBuilder('bo')
                ->where('bo.branch = :branch')
                ->andWhere('bo.end >= :date')
                ->orderBy('bo.begin')
                ->setMaxResults(1)
                ->setParameter('branch', $branch)
                ->setParameter('date', $date)
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     * Finds the previous occurrence for a branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence BranchOccurrence
     *
     * @return BranchOccurrence|null
     */
    public function findOnePreviousForBranchOccurrence(BranchOccurrence $branchOccurrence)
    {
        return $this->createQueryBuilder('bo')
            ->where('bo.branch = :branch')
            ->andWhere('bo.begin < :date')
            ->orderBy('bo.begin', 'DESC')
            ->setParameter('branch', $branchOccurrence->getBranch())
            ->setParameter('date', $branchOccurrence->getBegin())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds next occurrence for a branch
     *
     * @param Branch  $branch Branch
     * @param boolean $open   Open
     *
     * @return BranchOccurrence|null
     */
    public function findOneNextForBranch(Branch $branch, $open = true)
    {
        $date = new \DateTime();

        if ($open) {
            $date->add(new \DateInterval(
                sprintf('PT%sS', $branch->getAssociation()->getClosingDelay())
            ));
        }

        return $this->createQueryBuilder('bo')
            ->where('bo.branch = :branch')
            ->andWhere('bo.begin >= :date')
            ->orderBy('bo.begin')
            ->setMaxResults(1)
            ->setParameter('branch', $branch)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds next occurrences for a branch
     *
     * @param Branch  $branch Branch
     * @param boolean $open   Open
     * @param integer $limit  Limit
     *
     * @return array
     */
    public function findAllNextForBranch(Branch $branch, $open = true, $limit = 5)
    {
        $date = new \DateTime();

        if ($open) {
            $date->add(new \DateInterval(
                sprintf('PT%sS', $branch->getAssociation()->getClosingDelay())
            ));
        }

        $qb = $this->createQueryBuilder('bo')
            ->where('bo.branch = :branch')
            ->andWhere('bo.begin >= :date')
            ->orderBy('bo.begin')
            ->setParameter('branch', $branch)
            ->setParameter('date', $date);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds in progress for a branch (if exists)
     *
     * @param Branch $branch Branch
     *
     * @return BranchOccurrence|null
     */
    public function findOneInProgressForBranch(Branch $branch)
    {
        $date1 = new \DateTime();
        $date1->add(new \DateInterval(
            sprintf('PT%sS', $branch->getAssociation()->getClosingDelay())
        ));

        $date2 = new \DateTime();
        $date2->sub(new \DateInterval(
            sprintf('PT%sS', $branch->getAssociation()->getOpeningDelay())
        ));

        return $this->createQueryBuilder('bo')
            ->where('bo.branch = :branch')
            ->andWhere('bo.begin < :date1')
            ->andWhere('bo.end >= :date2')
            ->orderBy('bo.begin')
            ->setMaxResults(1)
            ->setParameter('branch', $branch)
            ->setParameter('date1', $date1)
            ->setParameter('date2', $date2)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds all latest occurrences for an association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findAllLatestForAssociation(Association $association)
    {
        $stop = new \DateTime();
        $stop->add(new \DateInterval(sprintf('PT%sS', $association->getOpeningDelay())));

        return $this->createQueryBuilder('bo')
                ->addSelect('b')
                ->innerJoin('bo.branch', 'b')
                ->where('b.association = :association')
                ->andWhere('bo.end <= :stop')
                ->addOrderBy('bo.begin', 'DESC')
                ->addOrderBy('b.name', 'ASC')
                ->setParameter('association', $association)
                ->setParameter('stop', $stop)
                ->getQuery()
                ->getResult();
    }

    /**
     * Finds farthest occurrences for a branch
     *
     * @param Branch  $branh
     * @param integer $limit
     *
     * @return array
     */
    public function findFarthestForBranch(Branch $branch, $limit = null)
    {
        $qb = $this->createQueryBuilder('bo')
            ->where('bo.branch = :branch')
            ->setParameter('branch', $branch)
            ->addOrderBy('bo.begin', 'DESC');

        if (null != $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Returns true if $branchOccurrence is overlapping another occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     *
     * @return boolean
     */
    public function isOverlapping(BranchOccurrence $branchOccurrence)
    {
        $qb = $this->createQueryBuilder('bo');

        $qb->select('COUNT(bo.id)')
            ->where('bo.branch = :branch')
            ->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->andx(
                        $qb->expr()->lte('bo.begin', ':begin'),
                        $qb->expr()->gte('bo.end', ':begin')
                    ),
                    $qb->expr()->andx(
                        $qb->expr()->lte('bo.begin', ':end'),
                        $qb->expr()->gte('bo.end', ':end')
                    )
                )
            )
            ->setParameter('branch', $branchOccurrence->getBranch())
            ->setParameter('begin', $branchOccurrence->getBegin())
            ->setParameter('end', $branchOccurrence->getEnd());

        if (null !== $branchOccurrence->getId()) {
            $qb->andWhere('bo != :branchOccurrence')
                ->setParameter('branchOccurrence', $branchOccurrence);
        }

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
