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
     * @param \Doctrine\Common\Collections\Collection $branches
     *
     * @return array
     */
    public function findProducersForBranches($branches)
    {

    }

    /**
     * Returns query builder to find consumers of branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     *
     * @return QueryBuilder
     */
    public function getConsumersForBranchesQueryBuilder($branches)
    {
        return $this->filterConsumersForBranches($branches);
    }

    /**
     * Returns consumers of branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     *
     * @return array Consumers
     */
    public function findConsumersForBranches($branches)
    {
        return $this->getConsumersForBranchesQueryBuilder($branches)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters consumers of branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param QueryBuilder                            $qb
     *
     * @return QueryBuilder
     */
    public function filterConsumersForBranches($branches, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        $branchesIds = array();
        foreach ($branches as $branch) {
            $branchesIds[] = $branch->getId();
        }

        return $qb->join('u.salesOrders', 's')
            ->join('s.branchOccurrence', 'bo')
            ->join('bo.branch', 'b', Expr\Join::WITH, $qb->expr()->in('b.id', $branchesIds));
    }
}
