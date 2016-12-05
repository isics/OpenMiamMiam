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
        /** @var \Isics\Bundle\OpenMiamMiamBundle\Model\Cart\CartItem $cartItem */

        // Validates that product exists in branch
        if (!$cartItem->getProduct()->getBranches()->contains($cartItem->getCart()->getBranch())) {
            $this->context
                ->buildViolation('error.cart.product_unavailable_in_branch', array('branch' => $cartItem->getCart()->getBranch()->getName()))
                ->atPath('quantity')
                ->addViolation();
        }

        // Validates that product is available
        if (!$this->branchOccurrenceManager->getProductAvailabilityForNext($cartItem->getCart()->getBranch(), $cartItem->getProduct())->isAvailable()) {
            $this->context
                ->buildViolation('error.cart.product_unavailable')
                ->atPath('quantity')
                ->addViolation();
        }

        // Validates stock
        if (Product::AVAILABILITY_ACCORDING_TO_STOCK === $cartItem->getProduct()->getAvailability() && $cartItem->getProduct()->getStock() < $cartItem->getQuantity()) {
            $this->context
                ->buildViolation('error.cart.not_enough_stock', array('rest' => $cartItem->getProduct()->getStock()))
                ->atPath('quantity')
                ->addViolation();
        }

        // Validate decimal quantity
        $quantity = $cartItem->getQuantity();
        if ((intval($quantity) != floatval($quantity)) && !$cartItem->getProduct()->getAllowDecimalQuantity()) {
            $this->context
                ->buildViolation('error.cart.decimal_quantity_not_allowed')
                ->atPath('quantity')
                ->addViolation();
        }
    }
}
