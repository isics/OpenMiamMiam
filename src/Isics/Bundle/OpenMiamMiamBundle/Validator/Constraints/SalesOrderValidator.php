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

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SalesOrderValidator extends ConstraintValidator
{
    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * Constructs validator
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     *
     * @param SalesOrder $order
     * @param Constraint $constraint
     */
    public function validate($order, Constraint $constraint)
    {
        // Unique ref for association
        if (!$this->objectManager->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->isRefUnique($order)) {
            $this->context->addViolationAt('ref', $constraint->notUniqueRefMessage, array(), null);
        }
    }
}
