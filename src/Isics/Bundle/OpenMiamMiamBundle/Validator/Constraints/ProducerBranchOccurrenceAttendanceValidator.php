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

class ProducerBranchOccurrenceAttendanceValidator extends ConstraintValidator
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
     * @param ProducerBranchOccurrenceAttendance $attendance
     * @param Constraint $constraint
     */
    public function validate($attendance, Constraint $constraint)
    {
        // Validate branchOccurrence
        if (!in_array($attendance->getBranchOccurrence()->getBranch(), $attendance->getProducer()->getBranches())) {
            $this->context->addViolationAt('brancheOccurrence', 'error.calendar.invalid_branch_occurrence');
        }

        // Validate ProducerAttendance
        $producerAttendance = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:ProducerAttendance')
                ->findOneBy(array(
                    'producer' => $attendance->getProducer(),
                    'branchOccurrence' => $attendance->getBranchOccurrence()
                ));

        if ($producerAttendance != $attendance->getProducerAttendance()) {
            $this->context->addViolationAt('brancheOccurrence', 'error.calendar.invalid_producer_attendance');
        }
    }
}
