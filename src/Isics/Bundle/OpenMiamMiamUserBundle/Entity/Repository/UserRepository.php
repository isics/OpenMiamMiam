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
     * @param int                                     $lastOrderNbDaysConsideringCustomer
     *
     * @return QueryBuilder
     */
    public function getConsumersForBranchesQueryBuilder($branches, $lastOrderNbDaysConsideringCustomer = null)
    {
         $qb = $this->createQueryBuilder('u');

        $branchesIds = array();
        foreach ($branches as $branch) {
            $branchesIds[] = $branch->getId();
        }

        $qb->join('u.salesOrders', 'so')
            ->join('so.branchOccurrence', 'bo')
            ->join('bo.branch', 'b', Expr\Join::WITH, $qb->expr()->in('b.id', $branchesIds));

        if (null !== $lastOrderNbDaysConsideringCustomer) {
            $now = new \DateTime();
            $begin = new \DateTime("-".$lastOrderNbDaysConsideringCustomer." day");
            $qb->where('so.date > :from')
                ->andWhere('so.date < :to')
                ->setParameter('from', $begin)
                ->setParameter('to', $now);
        }

        return $qb;
    }

    /**
     * Returns query builder to find consumers without branch
     * 
     * @return QueryBuilder
     */
    public function getConsumersWithoutBranchQueryBuilder()
    {

        return $this->createQueryBuilder('u')
                    ->leftJoin('u.salesOrders', 'so')
                    ->andWhere('so.id IS NULL');
    }

    /**
     * Returns consumers of branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param int                                     $lastOrderNbDaysConsideringCustomer
     *
     * @return array Consumers
     */
    public function findConsumersForBranches($branches, $lastOrderNbDaysConsideringCustomer = null)
    {

        return $this
                ->getConsumersForBranchesQueryBuilder($branches, $lastOrderNbDaysConsideringCustomer)
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns mail orders open subscribers for branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param int                                     $lastOrderNbDaysConsideringCustomer
     *
     * @return array Subscribers
     */
    public function findMailOrdersOpenSubscribersForBranches($branches, $lastOrderNbDaysConsideringCustomer = null)
    {

        return $this
                ->getConsumersForBranchesQueryBuilder($branches, $lastOrderNbDaysConsideringCustomer)
                ->andWhere('u.isOrdersOpenNotificationSubscriber = true')
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns newsletter subscribers for branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param int                                     $lastOrderNbDaysConsideringCustomer
     *
     * @return array Subscribers
     */
    public function findNewsletterSubscribersForBranches($branches, $lastOrderNbDaysConsideringCustomer = null)
    {

        return $this
                ->getConsumersForBranchesQueryBuilder($branches, $lastOrderNbDaysConsideringCustomer);
                ->andWhere('u.isNewsletterSubscriber = true')
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns consumers without branch
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
     * Returns newsletter subscribers without branch
     *
     * @return array subscribers
     */
    public function findNewsletterSubscribersWithoutBranch()
    {
        return $this->getConsumersWithoutBranchQueryBuilder()
            ->andWhere('u.isNewsletterSubscriber = true')
            ->getQuery()
            ->getResult();
    }
}
