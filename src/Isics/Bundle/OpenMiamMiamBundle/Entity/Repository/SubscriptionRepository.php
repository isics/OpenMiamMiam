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

class SubscriptionRepository extends EntityRepository
{
    /**
     * Returns subscriptions for association
     *
     * @param Association $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForAssociationQueryBuilder(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('s') : $qb;

        return $qb->leftJoin('s.user', 'u')
                ->andWhere('s.association = :association')
                ->setParameter('association', $association)
                ->addOrderBy('u.id');
    }

    /**
     * Filters users by id
     *
     * @param QueryBuilder $qb
     * @param integer $ref
     *
     * @return QueryBuilder
     */
    public function refFilter(QueryBuilder $qb, $ref)
    {
        if ($ref !== null) {
            return $qb
                ->andWhere('u.id = :ref')
                ->setParameter('ref', $ref);
        }

        return $qb;
    }

    /**
     * Filters users by last name
     *
     * @param QueryBuilder $qb
     * @param string $lastName
     *
     * @return QueryBuilder
     */
    public function lastNameFilter(QueryBuilder $qb, $lastName)
    {
        if ($lastName !== null) {
            return $qb
                ->andWhere('u.lastname LIKE :lastName')
                ->setParameter('lastName', '%'.$lastName.'%');
        }

        return $qb;
    }

    /**
     * Filters users by first name
     *
     * @param QueryBuilder $qb
     * @param $firstName
     *
     * @return QueryBuilder
     */
    public function firstNameFilter(QueryBuilder $qb, $firstName)
    {
        if ($firstName !== null) {
            return $qb
                ->andWhere('u.firstname LIKE :firstName')
                ->setParameter('firstName', '%'.$firstName.'%');
        }

        return $qb;
    }

    /**
     * Filters users and returns the creditors
     *
     * @param QueryBuilder $qb
     * @param boolean $creditor
     *
     * @return QueryBuilder
     */
    public function creditorFilter(QueryBuilder $qb, $creditor)
    {
        if ($creditor == true) {
            return $qb->andWhere('s.credit < 0');
        }

        return $qb;
    }

    /**
     * Filters users and returns the users who are not deleted
     *
     * @param QueryBuilder $qb
     * @param boolean      $showDeleted
     *
     * @return QueryBuilder
     */
    public function deletedFilter(QueryBuilder $qb, $showDeleted = false)
    {
        if (false === $showDeleted) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('u'),
                $qb->expr()->eq('u.locked', 0)
            ));
        }

        return $qb;
    }
}
