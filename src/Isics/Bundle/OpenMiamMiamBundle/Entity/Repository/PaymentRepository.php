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
    public function findToAllocatedForUser(User $user)
    {
        return $this->createQueryBuilder('p')
                ->andWhere('p.rest > 0')
                ->andWhere('p.user = :user')
                ->setParameter('user', $user)
                ->addOrderBy('p.date', 'ASC')
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns amount of payments for a user and association
     *
     * @param User $user
     * @param Association $association
     *
     * @return float
     */
    public function getAmountForUserAndAssociation(User $user, Association $association)
    {
        $result = $this->createQueryBuilder('p')
                ->select('SUM(p.amount) AS amountSum')
                ->andWhere('p.association = :association')
                ->setParameter('association', $association)
                ->andWhere('p.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleResult();

        return $result['amountSum'];
    }

    /**
     * Returns query builder to find payments for a user and association
     *
     * @param User $user
     * @param Association $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForConsumerAndAssociationQueryBuilder(User $user, Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->andWhere('p.user = :user')
                ->andWhere('p.association = :association')
                ->setParameter('user', $user)
                ->setParameter('association', $association)
               ->addOrderBy('p.date', 'DESC');
    }
}
