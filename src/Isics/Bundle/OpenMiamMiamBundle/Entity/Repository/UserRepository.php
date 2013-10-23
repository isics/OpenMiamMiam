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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder

class UserRepository extends EntityRepository
{

    /**
     * Return Producer of an association
     *
     *@param Association $association
     * 
     * @return array
     */
    public function findProducerForAssociation(Association $association)
    {

    }

    /**
     * Return Producer of an branch
     *
     *@param Branch $branch
     * 
     * @return array
     */
    public function findProducerForBranch(Branch $branch)
    {

    }

    /**
     * Returns query builder to find consumers of an association
     *
     * @param Association $association
     *
     * @return QueryBuilder
     */
    public function getConsumersForAssociationQueryBuilder(Association $association)
    {
        return $this->filterConsumerForAssociation($association);
    }

    /**
     * Returns consumers of an association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findConsumersForAssociation(Association $association)
    {
        return $this->getConsumersForAssociationQueryBuilder($association)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters consumers of an branch
     *
     * @param Branch        $branch
     * @param QueryBuilder  $qb
     * @param string        $alias
     *
     * @return QueryBuilder
     */
    public function filterConsumersForAssociation(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->innerJoin('u.subscription', 's')
            ->setParameter('association', $association);
    }

    /**
     * Returns query builder to find consumers of an branch
     *
     * @param Branch $branch
     *
     * @return QueryBuilder
     */
    public function getConsumersForBranchQueryBuilder(Branch $branch)
    {
        return $this->filterConsumersForBranch($branch);
    }

    /**
     * Returns consumers of an branch
     *
     * @param Branch $branch
     *
     * @return array
     */
    public function findConsumersForBranch(Branch $branch)
    {
        return $this->getConsumersForBranchQueryBuilder($branch)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters consumers of an branch
     *
     * @param Branch        $branch
     * @param QueryBuilder  $qb
     * @param string        $alias
     *
     * @return QueryBuilder
     */
    public function filterConsumersForBranch(Branch $branch, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->Join('u.sales_order', 's')
            ->Join('s.branch_occurence', 'bo')
            ->Join('bo.branch', 'b', Expr\Join::WITH, $qb->expr()->eq('b', ':branch'))
            ->setParameter('branch', $branch);
    }
}
