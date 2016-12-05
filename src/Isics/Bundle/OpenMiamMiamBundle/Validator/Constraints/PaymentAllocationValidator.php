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

class PaymentAllocationValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     */
    public function validate($paymentAllocation, Constraint $constraint)
    {
        $salesOrder = $paymentAllocation->getSalesOrder();
        $payment = $paymentAllocation->getPayment();
        if (null !== $salesOrder && $salesOrder->getLeftToPay() < $paymentAllocation->getAmount()) {
            $this->context
                ->buildViolation('error.payment_allocation.amount_invalid', array('%max%' => $salesOrder->getLeftToPay()))
                ->atPath('amount')
                ->addViolation();
        } elseif (null !== $payment && $payment->getRest() < $paymentAllocation->getAmount()) {
            $this->context
                ->buildViolation('error.payment_allocation.amount_invalid', array('%max%' => $payment->getRest()))
                ->atPath('amount')
                ->addViolation();
        }
    }
}
