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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Manager\BranchOccurrenceManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CartItemValidator extends ConstraintValidator
{
    /**
     * @var BranchOccurrenceManager
     */
    protected $branchOccurrenceManager;

    /**
     * Constructor
     */
    public function __construct(BranchOccurrenceManager $branchOccurrenceManager)
    {
        $this->branchOccurrenceManager = $branchOccurrenceManager;
    }

    /**
     * @inheritdoc
     */
    public function validate($cartItem, Constraint $constraint)
    {
        // Validates that product exists in branch
        if (!$cartItem->getProduct()->getBranches()->contains($cartItem->getCart()->getBranch())) {
            $this->context->addViolationAt('product', sprintf(
                'Product unavailable in branch "%s"!',
                $cartItem->getCart()->getBranch()->getName()
            ));
        }

        // Validates that product is available
        if (!$this->branchOccurrenceManager->getProductAvailability($cartItem->getCart()->getBranch(), $cartItem->getProduct())->isAvailable()) {
            $this->context->addViolationAt('product', 'Product unavailable!');
        }

        // Validates stock
        if (Product::AVAILABILITY_ACCORDING_TO_STOCK === $cartItem->getProduct()->getAvailability() && $cartItem->getProduct()->getStock() < $cartItem->getQuantity()) {
            $this->context->addViolationAt('product', sprintf(
                'Not enough stock! (remaining: %s)',
                $cartItem->getProduct()->getStock()
            ));
        }
    }
}
