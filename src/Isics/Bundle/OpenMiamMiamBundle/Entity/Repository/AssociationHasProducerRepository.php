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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Doctrine\ORM\QueryBuilder;

class AssociationHasProducerRepository extends EntityRepository
{
    /**
     * Returns query builder to find all producer of association
     *
     * @param Association  $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForAssociationQueryBuilder(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('ahp') : $qb;

        return $qb->where('ahp.association = :association')
            ->setParameter('association', $association);
    }
}
