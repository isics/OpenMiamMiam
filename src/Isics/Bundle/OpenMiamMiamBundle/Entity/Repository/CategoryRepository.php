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
                ->add('from', 'IsicsOpenMiamMiamBundle:Category node, IsicsOpenMiamMiamBundle:Product p')
                ->innerJoin('p.category', 'pc')
                ->innerJoin('p.branches', 'b')
                ->andWhere('p.availability != :availability')
                ->andWhere('b = :branch')
                ->andWhere('pc.lft >= node.lft')
                ->andWhere('pc.rgt <= node.rgt')
                ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
                ->setParameter('branch', $branch)
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns true if category has products to display
     *
     * @param Branch $branch Branch
     * @param Category $category
     *
     * @return array
     */
    public function hasProductAvailableInBranch(Branch $branch, Category $category)
    {
        $result = $this->createQueryBuilder('c')
                ->addSelect('COUNT(c.id) AS counter')
                ->add('from', 'IsicsOpenMiamMiamBundle:Category c, IsicsOpenMiamMiamBundle:Product p')
                ->innerJoin('p.category', 'pc')
                ->innerJoin('p.branches', 'b')
                ->where('c.id = :categoryId')
                ->andWhere('p.availability != :availability')
                ->andWhere('b = :branch')
                ->andWhere('pc.lft >= c.lft')
                ->andWhere('pc.rgt <= c.rgt')
                ->groupBy('c.id')
                ->setParameter('categoryId', $category->getId())
                ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
                ->setParameter('branch', $branch)
                ->getQuery()
                ->getSingleResult();

        return $result['counter'] > 0;
    }
}
