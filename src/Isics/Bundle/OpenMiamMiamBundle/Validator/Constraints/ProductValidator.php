<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product as ProductEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProductValidator extends ConstraintValidator
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs validator
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     *
     * @param Product $product
     * @param Constraint $constraint
     */
    public function validate($product, Constraint $constraint)
    {
        // Branches validation
        $availableBranches = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:Branch')
                ->findForProducer($product->getProducer());

        foreach ($product->getBranches() as $branch) {
            if (!in_array($branch, $availableBranches)) {
                $this->context->addViolationAt('Branches', 'error.product.invalid_branches');
            }
        }

        // Price validation
        if ($product->getHasNoPrice()) {
            $product->setPrice(null);
        } elseif (null === $product->getPrice()) {
            $this->context->addViolationAt('price', 'error.required');
        }

        // Stock validation
        if (ProductEntity::AVAILABILITY_ACCORDING_TO_STOCK !== $product->getAvailability()) {
            $product->setStock(null);
        } elseif (null === $product->getStock()) {
            $this->context->addViolationAt('stock', 'error.required');
        }

        // Availability date validation
        if (ProductEntity::AVAILABILITY_AVAILABLE_AT !== $product->getAvailability()) {
            $product->setAvailableAt(null);
        } elseif (null === $product->getAvailableAt()) {
            $this->context->addViolationAt('availableAt', 'error.required');
        }

        // Category is leaf
        if ($product->getCategory()->getRgt()-$product->getCategory()->getlft() > 1) {
            $this->context->addViolationAt('category', 'error_product.category_not_a_leaf');
        }
    }
}
