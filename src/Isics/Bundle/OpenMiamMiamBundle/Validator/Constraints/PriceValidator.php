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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PriceValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     *
     * @param Product $product
     * @param Constraint $constraint
     */
    public function validate($product, Constraint $constraint)
    {
        if (!$product->getHasPrice()) {
            $product->setPrice(null);
        }

        if ($product->getHasPrice() && null === $product->getPrice()) {
            $this->context->addViolationAt('price', $constraint->message, array(), null);
        }
    }
}
