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
        $cart = $cartItem->getCart();
        $product = $cartItem->getProduct();

        // Validates that product exists in branch
        if (!$product->getBranches()->contains($cart->getBranch())) {
            $this->context->addViolationAt('product', 'error.cart.product_unavailable_in_branch', array('branch' => $cart->getBranch()->getName()));
        }

        // Validates that product is available
        if (!$this->branchOccurrenceManager->getProductAvailabilityForNext($cart->getBranch(), $product)->isAvailable()) {
            $this->context->addViolationAt('product', 'error.cart.product_unavailable');
        }

        // Validates stock
        if (Product::AVAILABILITY_ACCORDING_TO_STOCK === $product->getAvailability() && $product->getStock() < $cartItem->getQuantity()) {
            $this->context->addViolationAt('product', 'error.cart.not_enough_stock', array('rest' => $product->getStock()));
        }

        // Validate decimal quantity
        $quantity = $cartItem->getQuantity();
        if ((intval($quantity) != floatval($quantity)) && !$product->getAllowDecimalQuantity()) {
            $this->context->addViolationAt('product', 'error.cart.decimal_quantity_not_allowed');
        }

        // Test availability
        if ($product->getAvailability() === Product::AVAILABILITY_AVAILABLE_AT && $product->getAvailableAt() >= (new \DateTime())->setTime(0,0)) {
            $this->context->addViolationAt('product', 'error.cart.available_at', array('%date%' => $product->getAvailableAt()->format('d/m/Y')));
        }
    }
}
