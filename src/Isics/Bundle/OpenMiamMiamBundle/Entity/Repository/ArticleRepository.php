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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Article;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;

class ArticleRepository extends EntityRepository
{
    /**
     * Returns articles of an association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findForAssociation(Association $association)
    {
        return $this->filterAssociation($association)
            ->addOrderBy('a.publishedAt', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns published articles and visible in branch
     *
     * @param Branch   $branch
     * @param interger $limit
     *
     * @return array
     */
    public function findPublishedForBranch(Branch $branch, $limit = 3)
    {
        return $this->filterPublished($this->filterBranch($branch))
            ->setMaxResults($limit)
            ->addOrderBy('a.publishedAt', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters articles of a association
     *
     * @param Association  $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function filterAssociation(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('a') : $qb;

        return $qb->andWhere('a.association = :association')
            ->setParameter('association', $association);
    }

    /**
     * Filters articles of a branch
     *
     * @param Branch       $branch
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function filterBranch(Branch $branch, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('a') : $qb;

        return $qb->innerJoin('a.branches', 'b', Expr\Join::WITH, $qb->expr()->eq('b', ':branch'))
            ->setParameter('branch', $branch);
    }

    /**
     * Filters published articles
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function filterPublished(QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('a') : $qb;

        return $qb->andWhere('a.isPublished = true');
    }
}
