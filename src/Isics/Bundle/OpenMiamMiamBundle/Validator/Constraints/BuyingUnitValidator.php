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

class BuyingUnitValidator extends ConstraintValidator
{
    /**
     * @var array $buyingUnits
     */
    protected $buyingUnits;

    /**
     * Constructs validator
     *
     * @param array $buyingUnits
     */
    public function __construct(array $buyingUnits = array())
    {
        $this->buyingUnits = $buyingUnits;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (null !== $value && !in_array($value, $this->buyingUnits)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
