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

class PaymentValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     */
    public function validate($payment, Constraint $constraint)
    {
        $allocatedAmount = 0;
        foreach ($payment->getPaymentAllocations() as $allocation) {
            $allocatedAmount += $allocation->getAmount();
        }

        if ($payment->getAmount() < $allocatedAmount) {
            $this->context->addViolationAt('amount', 'error.payment.amount_invalid', array('%min%' => $allocatedAmount));
        }
    }
}
