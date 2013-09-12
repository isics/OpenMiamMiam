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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

class ProductRepository extends EntityRepository
{
    /**
     * Finds products visible in a branch and a category
     *
     * @param Branch   $branch   Branch
     * @param Category $category Category
     *
     * @return array
     */
    public function findAllVisibleInBranchAndCategory(Branch $branch, Category $category)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('pr')
            ->innerJoin('p.branches', 'b')
            ->innerJoin('p.producer', 'pr')
            ->where('p.availability != :availability')
            ->andWhere('b = :branch')
            ->andWhere('p.category = :category')
            ->addOrderBy('p.name')
            ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
            ->setParameter('branch', $branch)
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds a product visible in a branch by its slug
     *
     * @param string   $slug     Slug
     * @param Branch   $branch   Branch
     *
     * @return Product|null
     */
    public function findOneBySlugAndVisibleInBranch($slug, Branch $branch)
    {
        try {
            return $this->createQueryBuilder('p')
                ->addSelect('pr')
                ->innerJoin('p.branches', 'b')
                ->innerJoin('p.producer', 'pr')
                ->where('p.slug = :slug')
                ->andwhere('p.availability != :availability')
                ->andWhere('b = :branch')
                ->addOrderBy('p.name')
                ->setParameter('slug', $slug)
                ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
                ->setParameter('branch', $branch)
                ->getQuery()
                ->getSingleResult();

        } catch (\Doctrine\ORM\NoResultException $e) {

            return null;
        }
    }

    /**
     * Returns producer's products
     *
     * @param Producer $producer
     *
     * @return array
     */
    public function findForProducer(Producer $producer)
    {
        return $this->createQueryBuilder('p')
                ->addSelect('b')
                ->innerJoin('p.branches', 'b')
                ->where('p.producer = :producer')
                ->setParameter('producer', $producer)
                ->getQuery()
                ->getResult();
    }
}
