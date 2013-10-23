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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter
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
     * Return Consumer of an Association
     *
     *@param Association $association
     *@param QueryBuilder $qb
     *
     *@return QueryBuilder
     */
    public function findConsumerForAssociation(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->innerJoin('u.subscription', 's', Expr\Join::WITH, $qb->expr()->eq('a', ':association'))
                    ->setParameter('association', $association);
    }

    /**
     * Return Consumer of an branch
     *
     *@param Branch $branch
     *@param QueryBuilder $qb
     *
     *@return QueryBuilder
     */
    public function findConsumerForBranch(Branch $branch, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->innerJoin('u.sales_order', 's', Expr\Join::ON, 'u.id = s.user_id')
                    ->innerJoin('s.branch_occurence', 'bo', Expr\Join::ON, 's.branch_occurence_id = bo.id'))
                    ->innerJoin('bo.branch', 'b', Expr\Join::WITH, $qb->expr()->eq('b', ':branch'))
                    ->setParameter('branch', $branch);             
    }
}
