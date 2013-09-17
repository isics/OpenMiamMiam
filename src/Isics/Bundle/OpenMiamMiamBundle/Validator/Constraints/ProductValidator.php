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

use Doctrine\Common\Persistence\ObjectManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product as ProductEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProductValidator extends ConstraintValidator
{
    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * Constructs validator
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
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
        $availableBranches = $this->objectManager
                ->getRepository('IsicsOpenMiamMiamBundle:Branch')
                ->findForProducer($product->getProducer());

        foreach ($product->getBranches() as $branch) {
            if (!in_array($branch, $availableBranches)) {
                $this->context->addViolationAt('Branches', $constraint->invalidBranchesMessage, array(), null);
            }
        }

        // Price validation
        if (!$product->getHasPrice()) {
            $product->setPrice(null);
        } elseif (null === $product->getPrice()) {
            $this->context->addViolationAt('price', $constraint->requiredMessage, array(), null);
        }

        // Stock validation
        if (ProductEntity::AVAILABILITY_ACCORDING_TO_STOCK !== $product->getAvailability()) {
            $product->setStock(null);
        } elseif (null === $product->getStock()) {
            $this->context->addViolationAt('stock', $constraint->requiredMessage, array(), null);
        }

        // Availability date validation
        if (ProductEntity::AVAILABILITY_AVAILABLE_AT !== $product->getAvailability()) {
            $product->setAvailableAt(null);
        } elseif (null === $product->getAvailableAt()) {
            $this->context->addViolationAt('availableAt', $constraint->requiredMessage, array(), null);
        }
    }
}
