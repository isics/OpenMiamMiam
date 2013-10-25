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

class BranchOccurrenceValidator extends ConstraintValidator
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
     * @param BranchOccurrence $branchOccurrence
     * @param Constraint       $constraint
     */
    public function validate($branchOccurrence, Constraint $constraint)
    {
        // Begin > today
        if (!($branchOccurrence->getBegin() > new \DateTime())) {
            $this->context->addViolationAt('date', 'error.invalid');
        }

        // End > begin
        if (!($branchOccurrence->getEnd() > $branchOccurrence->getBegin())) {
            $this->context->addViolationAt('endTime', 'error.invalid');
        }

        // No overlapping
        if ($this->entityManager
            ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
            ->isOverlapping($branchOccurrence)) {
            $this->context->addViolationAt('date', 'error.invalid');
        }
    }
}
