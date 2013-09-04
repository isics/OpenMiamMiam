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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

class CategoryRepository extends EntityRepository
{
    /**
     * Finds categories available in a branch (categories with products)
     *
     * @param Branch $branch Branch
     *
     * @return array
     */
    public function findAllAvailableInBranch(Branch $branch)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.products', 'p')
            ->innerJoin('p.branches', 'b')
            ->where('p.availability != :availability')
            ->andWhere('b = :branch')
            ->groupBy('c.id')
            ->addOrderBy('c.name')
            ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
            ->setParameter('branch', $branch)
            ->getQuery()
            ->getResult();
    }
}
