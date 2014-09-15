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
     * Return next sibling
     *
     * @param Category $category
     *
     * @return Category
     */
    public function getNextSibling(Category $category)
    {
        return $this->getNextSiblingQueryBuilder($category)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return next sibling QueryBuilder
     *
     * @param Category $category
     *
     * @return QueryBuilder
     */
    public function getNextSiblingQueryBuilder(Category $category)
    {
        return $this->getNextSiblingsQueryBuilder($category)
            ->orderBy('node.lft')
            ->setMaxResults(1);
    }

    /**
     * Return prev sibling
     *
     * @param Category $category
     *
     * @return Category
     */
    public function getPrevSibling(Category $category)
    {
        return $this->getPrevSiblingQueryBuilder($category)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return prev sibling QueryBuilder
     *
     * @param Category $category
     *
     * @return QueryBuilder
     */
    public function getPrevSiblingQueryBuilder(Category $category)
    {
        return $this->getPrevSiblingsQueryBuilder($category)
            ->orderBy('node.lft', 'DESC')
            ->setMaxResults(1);
    }

    public function getCategoryAtLevel(Category $category, $level) {
        return $this
            ->createQueryBuilder('c')
            ->select('c')
            ->where('c.lvl = :level')
            ->andWhere('c.lft < :left')
            ->andWhere('c.rgt > :right')
            ->setParameter(':level', $level)
            ->setParameter(':left', $category->getLft())
            ->setParameter(':right', $category->getRgt())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Finds first level categories available in a branch
     *
     * @param Branch $branch Branch
     *
     * @return array
     */
    public function findLevel1WithProductsInBranch(Branch $branch)
    {
        return $this->createQueryBuilder('node')
            ->add('from', 'IsicsOpenMiamMiamBundle:Category node, IsicsOpenMiamMiamBundle:Product p')
            ->innerJoin('p.category', 'pc')
            ->innerJoin('p.branches', 'b')
            ->andWhere('p.availability != :availability')
            ->andWhere('b = :branch')
            ->andWhere('pc.lft >= node.lft')
            ->andWhere('pc.rgt <= node.rgt')
            ->andWhere('node.lvl = 1')
            ->addOrderBy('node.lft')
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
                ->select('COUNT(c.id) AS counter')
                ->add('from', 'IsicsOpenMiamMiamBundle:Category c, IsicsOpenMiamMiamBundle:Product p')
                ->innerJoin('p.category', 'pc')
                ->innerJoin('p.branches', 'b')
                ->where('c.id = :categoryId')
                ->andWhere('p.availability != :availability')
                ->andWhere('b = :branch')
                ->andWhere('pc.lft >= c.lft')
                ->andWhere('pc.rgt <= c.rgt')
                ->setParameter('categoryId', $category->getId())
                ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
                ->setParameter('branch', $branch)
                ->getQuery()
                ->getSingleResult();

        return $result['counter'] > 0;
    }
}
