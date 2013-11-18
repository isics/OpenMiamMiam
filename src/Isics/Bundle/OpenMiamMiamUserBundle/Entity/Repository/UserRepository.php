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
            JOIN %s e ON (e.security_identity_id = si.id)
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
     * Return users managing producer(s)
     *
     * @param mixed $producer Producer or array of Producer ids
     *
     * @return array
     */
    public function findManagingProducer($producer)
    {
        $producersIds = array();
        if (is_array($producer)) {
            foreach ($producer as $_producer) {
                $producersIds[] = $_producer;
            }
        } else {
            $producersIds[] = $producer->getId();
        }

        if (empty($producersIds)) {
            return array();
        }

        $query = <<<QUERY
            SELECT DISTINCT u.*
            FROM %s u
            JOIN %s si ON (si.identifier = CONCAT('%s-', u.username))
            JOIN %s e ON (e.security_identity_id = si.id)
            JOIN %s oi ON (oi.id = e.object_identity_id )
            JOIN %s c ON (c.id = oi.class_id)
            WHERE c.class_type = '%s'
            AND oi.object_identifier IN (%s)
            ORDER BY u.lastname
QUERY;

        $query = sprintf(
            $query,
            'fos_user',
            'acl_security_identities',
            addslashes('Isics\Bundle\OpenMiamMiamUserBundle\Entity\User'),
            'acl_entries',
            'acl_object_identities',
            'acl_classes',
            addslashes('Isics\Bundle\OpenMiamMiamBundle\Entity\Producer'),
            implode(',', $producersIds)
        );

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('IsicsOpenMiamMiamUserBundle:User', 'u');

        return $this->getEntityManager()->createNativeQuery($query, $rsm)->getResult();
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
     * Returns query builder to find consumers without branch
     *
     * @return QueryBuilder
     */
    public function getConsumersWithoutBranchQueryBuilder()
    {
        return $this->filterConsumersWithoutOrder();
    }

    /**
     * Returns consumers of branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param int $lastOrderNbDaysConsideringCustomer
     *
     * @return array Consumers
     */
    public function findConsumersForBranches($branches, $lastOrderNbDaysConsideringCustomer = null)
    {
        $consumersForBranchesQueryBuilder = $this->getConsumersForBranchesQueryBuilder($branches);

        if (null !== $lastOrderNbDaysConsideringCustomer) {
            $now = new \DateTime();
            $begin = new \DateTime("-".$lastOrderNbDaysConsideringCustomer." day");
            $consumersForBranchesQueryBuilder
                ->where('s.date > :from')
                ->andWhere('s.date < :to')
                ->setParameter('from', $begin)
                ->setParameter('to', $now);
        }

        return $consumersForBranchesQueryBuilder->getQuery()->getResult();
    }

    /**
     * Returns consumers without branch
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     *
     * @return array Consumers
     */
    public function findConsumersWithoutBranch()
    {
        return $this->getConsumersWithoutBranchQueryBuilder()
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

    /**
     * Filters consumers without order/branch
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function filterConsumersWithoutOrder(QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->leftJoin('u.salesOrders', 's')
            ->andWhere('s.id IS NULL');
    }
}
