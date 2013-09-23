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

use Doctrine\ORM\Query\Expr\Join;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

class CategoryRepository extends NestedTreeRepository
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
        return $this->getRootNodesQueryBuilder()
//                ->innerJoin('node.products', 'p', Join::ON, 'p.id = 2')
//                ->innerJoin('p.branches', 'b')
//                ->where('p.availability != :availability')
//                ->andWhere('b = :branch')
//                ->groupBy('node.id')
//                ->addOrderBy('node.name')
//                ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
//                ->setParameter('branch', $branch)
                ->getQuery()
                ->getResult();
    }
}
