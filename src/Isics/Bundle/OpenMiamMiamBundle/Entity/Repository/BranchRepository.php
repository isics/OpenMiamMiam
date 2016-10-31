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
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
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
    public function getForAssociationWithProducersCountQueryBuilder(Association $association)
    {
        return $this->filterAssociation($association)
            ->addSelect('COUNT(p.id) AS nbProducers')
            ->leftJoin('b.associationProducers', 'ap')
            ->leftJoin('ap.producer', 'p')
            ->andWhere('p.deletedAt is null')
            ->groupBy('b.id')
            ->orderBy('b.city');
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
            ->orderBy('b.city');
    }

    /**
     * Returns query builder to find branches with number of producers
     *
     * @return QueryBuilder
     */
    public function getWithProducersCountQueryBuilder()
    {
        return $this->createQueryBuilder('b')
            ->addSelect('COUNT(p.id) AS nbProducers')
            ->innerJoin('b.associationProducers', 'ap')
            ->innerJoin('ap.producer', 'p')
            ->groupBy('b.id')
            ->orderBy('b.city');
    }

    /**
     * Returns branches for association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findForAssociationWithProducersCount(Association $association)
    {
        return $this->getForAssociationWithProducersCountQueryBuilder($association)
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
     * Returns branches with number of producers
     *
     * @return array
     */
    public function findWithProducersCount()
    {
        return $this->getWithProducersCountQueryBuilder()
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
     *
     * @return QueryBuilder
     */
    public function filterProducer(Producer $producer, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('b') : $qb;

        return $qb
            ->innerJoin('b.associationProducers', 'ap')
            ->innerJoin('ap.producer', 'p', Expr\Join::WITH, $qb->expr()->eq('p', ':producer'))
            ->setParameter('producer', $producer);
    }
}
