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
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
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
     * Finds a product visible in a branch by its id
     *
     * @param integer $id     id
     * @param Branch  $branch Branch
     *
     * @return Product|null
     */
    public function findOneByIdAndVisibleInBranch($id, Branch $branch)
    {
        try {
            return $this->createQueryBuilder('p')
                ->addSelect('pr')
                ->innerJoin('p.branches', 'b')
                ->innerJoin('p.producer', 'pr')
                ->where('p.id = :id')
                ->andwhere('p.availability != :availability')
                ->andWhere('b = :branch')
                ->addOrderBy('p.name')
                ->setParameter('id', $id)
                ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
                ->setParameter('branch', $branch)
                ->getQuery()
                ->getSingleResult();

        } catch (\Doctrine\ORM\NoResultException $e) {

            return null;
        }
    }

    /**
     * Finds products of the moment of a branch
     *
     * @param BranchOccurrence $branchOccurrence Branch occurrence
     * @param integer          $limit            Limit
     *
     * @return array
     */
    public function findOfTheMomentForBranchOccurrence(BranchOccurrence $branchOccurrence, $limit = 3)
    {
        // Retrieves all products of the moment ids and producer ids
        $qb = $this->filterAvailableForBranchOccurrence($branchOccurrence);
        $productsIds = $qb
            ->select('p.id as product_id, pr.id as producer_id')
            ->innerJoin('p.branches', 'b', Expr\Join::WITH, $qb->expr()->eq('b', ':branch'))
            ->andWhere('p.isOfTheMoment = true')
            ->setParameter('branch', $branchOccurrence->getBranch())
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
     * Returns query builder for producer products
     *
     * @param Producer $producer
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForProducerQueryBuilder(Producer $producer, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->addSelect('b')
                ->leftJoin('p.branches', 'b')
                ->andWhere('p.producer = :producer')
                ->setParameter('producer', $producer)
                ->addOrderBy('p.name');
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
        return $this->getForProducerQueryBuilder($producer)->getQuery()->getResult();
    }

    /**
     * Returns query builder for available products
     *
     * @param BranchOccurrence $branchOccurrence Branch occurrence
     * @param QueryBuilder     $qb
     *
     * @return QueryBuilder
     */
    public function filterAvailableForBranchOccurrence(BranchOccurrence $branchOccurrence, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->innerJoin('p.producer', 'pr')
            ->innerJoin('pr.producerAttendances', 'pra', Expr\Join::WITH, $qb->expr()->eq('pra.branchOccurrence', ':branchOccurrence'))
            ->andWhere('pra.isAttendee = true')
            ->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->eq('p.availability', ':available'),
                    $qb->expr()->andx(
                        $qb->expr()->eq('p.availability', ':accordingToStock'),
                        $qb->expr()->gt('p.stock', 0)
                    ),
                    $qb->expr()->andx(
                        $qb->expr()->eq('p.availability', ':availableAt'),
                        $qb->expr()->lt('p.availableAt', ':begin')
                    )
                )
            )
            ->setParameter('branchOccurrence', $branchOccurrence)
            ->setParameter('available', Product::AVAILABILITY_AVAILABLE)
            ->setParameter('accordingToStock', Product::AVAILABILITY_ACCORDING_TO_STOCK)
            ->setParameter('availableAt', Product::AVAILABILITY_AVAILABLE_AT)
            ->setParameter('begin', $branchOccurrence->getBegin());
    }

    /**
     * Returns query builder for association products
     *
     * @param Association $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForAssociationQueryBuilder(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->addSelect('b')
                ->innerJoin('p.producer', 'pr')
                ->innerJoin('pr.associations', 'a')
                ->leftJoin('p.branches', 'b')
                ->andWhere('a.id = :associationId')
                ->setParameter('associationId', $association->getId())
                ->addOrderBy('p.name')
                ->addGroupBy('p.id');
    }

    /**
     * Returns products of an association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findForAssociation(Association $association)
    {
        return $this->getForAssociationQueryBuilder($association)->getQuery()->getResult();
    }

    /**
     * Returns queryBuilder to find out of stock products of a producer
     *
     * @param Producer $producer
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getOutOfStockForProducerQueryBuilder(Producer $producer, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->where('p.producer = :producer')
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
