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

class SalesOrder extends Constraint
{
    public $notUniqueRefMessage = 'Ref is not unique.';

    /**
     * {@inheritDoc}
     *
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'open_miam_miam_sales_order_validator';
    }
}
