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

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SalesOrderValidator extends ConstraintValidator
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs validator
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        if (!$this->entityManager->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->isRefUnique($order)) {
            $this->context->addViolationAt('ref', $constraint->notUniqueRefMessage, array(), null);
        }
    }
}
