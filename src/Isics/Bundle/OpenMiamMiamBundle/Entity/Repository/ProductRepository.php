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
     * Finds products of the moment visible in a branch
     *
     * @param Branch  $branch Branch
     * @param integer $limit  Limit
     *
     * @todo Retrieve only products available for order
     *
     * @return array
     */
    public function findOfTheMomentForBranch(Branch $branch, $limit = 3)
    {
        // Retrieves all products of the moment ids and producer ids
        $productsIds = $this->createQueryBuilder('p')
            ->select('p.id as product_id, pr.id as producer_id')
            ->innerJoin('p.branches', 'b')
            ->innerJoin('p.producer', 'pr')
            ->where('p.availability != :availability')
            ->andWhere('b = :branch')
            ->andWhere('p.isOfTheMoment = true')
            ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
            ->setParameter('branch', $branch)
            ->getQuery()
            ->getResult();

        if (empty($productsIds)) {
            return array();
        }

        // Groups products by producer
        $productsIdsByProducer = array();
        foreach ($productsIds as $productIds) {
            if (!array_key_exists($productIds['producer_id'], $productsIdsByProducer)) {
                $productsIdsByProducer[$productIds['producer_id']] = array();
            }
            $productsIdsByProducer[$productIds['producer_id']][] = $productIds['product_id'];
        }

        // Randomizes producers
        shuffle($productsIdsByProducer);

        // Truncates
        array_splice($productsIdsByProducer, $limit);

        // Creates products ids (1 random by remaining producer)
        $productsIds = array();
        foreach ($productsIdsByProducer as $producerProductsIds) {
            $productsIds[] = $producerProductsIds[array_rand($producerProductsIds)];
        }

        // Retrieves products
        $products = $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $productsIds)
            ->getQuery()
            ->getResult();

        // Randomizes products
        shuffle($products);

        return $products;
    }

    /**
     * Returns products of a producer
     *
     * @param Producer $producer
     *
     * @return array
     */
    public function findForProducer(Producer $producer)
    {
        return $this->createQueryBuilder('p')
                ->addSelect('b')
                ->leftJoin('p.branches', 'b')
                ->where('p.producer = :producer')
                ->setParameter('producer', $producer)
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns queryBuilder to find out of stock products of a producer
     *
     * @param Producer $producer
     *
     * @return QueryBuilder
     */
    public function getOutOfStockForProducerQueryBuilder(Producer $producer)
    {
        return $this->createQueryBuilder('p')
                ->where('p.producer = :producer')
                ->setParameter('producer', $producer)
                ->andWhere('p.availability = :availability')
                ->setParameter('availability', Product::AVAILABILITY_ACCORDING_TO_STOCK)
                ->andWhere('p.stock <= 0');
    }

    /**
     * Returns count of out of stock products of a producer
     *
     * @param Producer $producer
     *
     * @return int
     */
    public function countOutOfStockProductsForProducer(Producer $producer)
    {
        $result = $this->getOutOfStockForProducerQueryBuilder($producer)
                ->select('COUNT(p.id) AS counter')
                ->getQuery()
                ->getSingleResult();

        return $result['counter'];
    }
}
