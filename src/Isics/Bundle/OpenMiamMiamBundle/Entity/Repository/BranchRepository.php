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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class BranchRepository extends EntityRepository
{
    /**
     * Returns query builder to find producer's branches
     *
     * @param Producer $producer
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBranchesForProducerQueryBuilder(Producer $producer)
    {
        return $this->createQueryBuilder('b')
                ->innerJoin('b.producers', 'p')
                ->where('p.id = :producerId')
                ->setParameter('producerId', $producer->getId())
                ->orderBy('b.name', 'ASC');
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
        return $this->getBranchesForProducerQueryBuilder($producer)
                ->getQuery()
                ->getResult();
    }
}
