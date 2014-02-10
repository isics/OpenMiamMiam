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
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class PaymentRepository extends EntityRepository
{
    /**
     * Returns payments to allocate for a user
     *
     * @param User $user
     *
     * @return array
     */
    public function findToAllocatedForUser(User $user = null)
    {
        $qb = $this->createQueryBuilder('p')->andWhere('p.rest > 0');

        if (null === $user) {
            $qb->andWhere($qb->expr()->isNull('p.user'));
        } else {
            $qb->andWhere('p.user = :user')->setParameter('user', $user);
        }

        return $qb->addOrderBy('p.date', 'ASC')->getQuery()->getResult();
    }

    /**
     * Returns amount of payments for a user and association
     *
     * @param Association $association
     * @param User $user
     *
     * @return float
     */
    public function getAmountForUserAndAssociation(Association $association, User $user = null)
    {
        $qb = $this->createQueryBuilder('p')
                ->select('SUM(p.amount) AS amountSum')
                ->andWhere('p.association = :association')
                ->setParameter('association', $association);

        if (null === $user) {
            $qb->andWhere($qb->expr()->isNull('p.user'));
        } else {
            $qb->andWhere('p.user = :user')
                ->setParameter('user', $user);
        }

        $result = $qb->getQuery()->getSingleResult();

        return $result['amountSum'];
    }

    /**
     * Returns query builder to find payments for a user and association
     *
     * @param Association $association
     * @param User $user
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForConsumerAndAssociationQueryBuilder(Association $association, User $user = null, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        $qb->andWhere('p.association = :association')->setParameter('association', $association);

        if (null === $user) {
            $qb->andWhere($qb->expr()->isNull('p.user'));
        } else {
            $qb->andWhere('p.user = :user')->setParameter('user', $user);
        }

        return $qb->addOrderBy('p.date', 'DESC');
    }

    /**
     * Returns statistics for sales orders
     *
     * @param array $salesOrders
     *
     * @return array
     */
    public function getStatisticsForSalesOrders(array $salesOrders)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->select('p.type AS type, SUM(pa.amount) AS amount, COUNT(p.id) AS counter')
                ->innerJoin('p.paymentAllocations', 'pa')
                ->andWhere($qb->expr()->in('pa.salesOrder', ':salesOrders'))
                ->setParameter('salesOrders', $salesOrders)
                ->groupBy('p.type')
                ->addOrderBy('p.type', 'ASC')
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns true if user has at least one payment with rest
     *
     * @param Association $association
     * @param User        $user
     *
     * @return bool
     */
    public function hasPaymentWithRestForUserAndAssociation(Association $association, User $user = null)
    {
        $qb = $this->createQueryBuilder('p')
                ->select('COUNT(p.id) AS counter')
                ->andWhere('p.association = :association')
                ->setParameter('association', $association)
                -> andWhere('p.rest > :minAmount')
                ->setParameter('minAmount', 0)
        ;

        if (null === $user) {
            $qb->andWhere($qb->expr()->isNull('p.user'));
        } else {
            $qb->andWhere('p.user = :user')->setParameter('user', $user);
        }

        $result = $qb->getQuery()->getSingleResult();

        return $result['counter'];
    }
}
