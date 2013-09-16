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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Product as ProductEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProductValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     *
     * @param Product $product
     * @param Constraint $constraint
     */
    public function validate($product, Constraint $constraint)
    {
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
    }
}
