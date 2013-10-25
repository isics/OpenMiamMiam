<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;

class UserRepository extends EntityRepository
{
    /**
     * Returns consumers for association
     *
     * @param Association $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForAssociationQueryBuilder(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->innerJoin('u.subscriptions', 's')
                ->andWhere('s.association = :association')
                ->setParameter('association', $association)
                ->addOrderBy('u.id')
                ->addGroupBy('u.id');
    }

    /**
     * Returns consumers for association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findForAssociation(Association $association)
    {
        return $this->getForAssociationQueryBuilder($association)
                ->getQuery()
                ->getResult();
    }

    /**
     * returns users with ACEs
     *
     * @return array
     */
    public function findWithACE()
    {
        $query = <<<QUERY
            SELECT DISTINCT u.*
            FROM %s u INNER JOIN %s si ON (si.identifier = CONCAT('%s-', u.username))
            INNER JOIN %s e ON (e.security_identity_id = si.id)
            ORDER BY u.lastname
QUERY;

        $query = sprintf(
            $query,
            'fos_user',
            'acl_security_identities',
            addslashes('Isics\Bundle\OpenMiamMiamUserBundle\Entity\User'),
            'acl_entries'
        );

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('IsicsOpenMiamMiamUserBundle:User', 'u');

        return $this->getEntityManager()->createNativeQuery($query, $rsm)->getResult();
    }

    /**
     * Return Producer of branches
     *
     *@param \Doctrine\Common\Collections\Collection $branches
     * 
     * @return array
     */
    public function findProducersForBranches($branches)
    {

    }

    /**
     * Returns query builder to find consumers of an branch
     *
     * @param Branch $branch
     *
     * @return QueryBuilder
     */
    public function getConsumersForBranchesQueryBuilder(Branch $branch)
    {
        return $this->filterConsumersForBranches($branch);
    }

    /**
     * Returns consumers of branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     *
     * @return array $consumers
     */
    public function findConsumersForBranches($branches)
    {
        foreach ($branches as $branch) {
            $consumers = $this->getConsumersForBranchesQueryBuilder($branch)
                        ->getQuery()
                        ->getResult();
        }
        return $consumers;
    }

    /**
     * Filters consumers of an branches
     *
     * @param Branch        $branch
     * @param QueryBuilder  $qb
     * @param string        $alias
     *
     * @return QueryBuilder
     */
    public function filterConsumersForBranches(Branch $branch, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->Join('u.salesOrders', 's')
            ->Join('s.branchOccurrence', 'bo')
            ->Join('bo.branch', 'b', Expr\Join::WITH, $qb->expr()->eq('b', ':branch'))
            ->setParameter('branch', $branch);
    }
}
