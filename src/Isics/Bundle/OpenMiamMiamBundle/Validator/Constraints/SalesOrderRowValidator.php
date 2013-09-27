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
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SalesOrderRowValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     *
     * @param SalesOrderRow $row
     * @param Constraint $constraint
     */
    public function validate($row, Constraint $constraint)
    {
        $product = $row->getProduct();
        if (null !== $product && $product->getAvailability() == Product::AVAILABILITY_ACCORDING_TO_STOCK) {
            if ($product->getStock()-($row->getQuantity()-$row->getOldQuantity()) < 0) {
                $this->context->addViolationAt('quantity', 'error.sales_order.not_enough_stock', array('rest' => $product->getStock()));
            }
        }
    }
}
