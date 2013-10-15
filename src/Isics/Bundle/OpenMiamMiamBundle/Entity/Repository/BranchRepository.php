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
use Doctrine\ORM\Query\Expr;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class BranchRepository extends EntityRepository
{
    /**
     * Returns query builder to find association's branches
     *
     * @param Association $association
     *
     * @return QueryBuilder
     */
    public function getForAssociationQueryBuilder(Association $association)
    {
        return $this->filterAssociation($association)
            ->orderBy('b.name');
    }

    /**
     * Returns query builder to find producer's branches
     *
     * @param Producer $producer
     *
     * @return QueryBuilder
     */
    public function getForProducerQueryBuilder(Producer $producer)
    {
        return $this->filterProducer($producer)
            ->orderBy('b.name');
    }

    /**
     * Returns branches for association
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
     * Returns branches for producer
     *
     * @param Producer $producer
     *
     * @return array
     */
    public function findForProducer(Producer $producer)
    {
        return $this->getForProducerQueryBuilder($producer)
                ->getQuery()
                ->getResult();
    }

    /**
     * Filters branches of an association
     *
     * @param Association  $association
     * @param QueryBuilder $qb
     * @param string       $alias
     *
     * @return QueryBuilder
     */
    public function filterAssociation(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('b') : $qb;

        return $qb->andWhere('b.association = :association')
            ->setParameter('association', $association);
    }

    /**
     * Filters branches of a producer
     *
     * @param Producer     $producer
     * @param QueryBuilder $qb
     * @param string       $alias
     *
     * @return QueryBuilder
     */
    public function filterProducer(Producer $producer, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('b') : $qb;

        return $qb->innerJoin('b.producers', 'p', Expr\Join::WITH, $qb->expr()->eq('p', ':producer'))
                ->setParameter('producer', $producer);
    }
}
