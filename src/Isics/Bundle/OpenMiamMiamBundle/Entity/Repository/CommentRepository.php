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

class CommentRepository extends EntityRepository
{
    /**
     * Returns query builder to find comments left on a user by an association
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
}
