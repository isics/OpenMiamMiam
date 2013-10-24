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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;

class NewsletterRepository extends entityRepository
{
    /**
     * Returns newsletter of an association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findForAssociation(Association $association)
    {
        return $this->filterAssociation($association)
        ->addOrderBy('n.sentAt', 'desc')
        ->getQuery()
        ->getResult();
    }
    
    /**
     * Returns newsletter of super
     *
     * @return array
     */
    public function findForSuper()
    {
        return $this->filterSuper()
        ->addOrderBy('n.sentAt', 'desc')
        ->getQuery()
        ->getResult();
    }
    
    /**
     * Filters newsletter of a association
     *
     * @param Association  $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function filterAssociation(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('n') : $qb;
    
        return $qb->andWhere('n.association = :association')
        ->setParameter('association', $association);
    }
    /**
     * Filters newsletter of super
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function filterSuper(QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('n') : $qb;
    
        return $qb->andWhere('n.association IS NULL');
    }
}