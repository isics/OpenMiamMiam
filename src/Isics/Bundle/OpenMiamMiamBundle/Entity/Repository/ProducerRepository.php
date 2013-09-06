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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;

class ProducerRepository extends EntityRepository
{
    /**
     * Finds next occurrences for a branch
     *
     * @param Branch  $branch Branch
     * @param integer $limit  Limit
     *
     * @return array
     */
    public function findAllRandomForBranch(Branch $branch, $limit = 5)
    {
        // Retrieve all producers ids
        $ids = $this->findAllIds();
        if (empty($ids)) {
            return array();
        }

        // Randomize ids
        shuffle($ids);

        // Truncate
        array_splice($ids, $limit);

        // Retrieve producers
        $producers = $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        // Randomize producers
        shuffle($producers);

        return $producers;
    }

    /**
     * Finds all ids
     *
     * @return array
     */
    public function findAllIds()
    {
        $ids = $this->createQueryBuilder('p')
                ->select('p.id')
                ->getQuery()
                ->getResult();

        $flattenIds = array();
        foreach ($ids as $id) {
            $flattenIds[] = $id['id'];
        }

        return $flattenIds;
    }
}
