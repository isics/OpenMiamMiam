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

class ProducerBranchOccurrenceAttendanceValidator extends ConstraintValidator
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
     * @param ProducerBranchOccurrenceAttendance $attendance
     * @param Constraint $constraint
     */
    public function validate($attendance, Constraint $constraint)
    {
        // Validate branchOccurrence
        if (!in_array($attendance->getBranchOccurrence()->getBranch(), $attendance->getProducer()->getBranches()->toArray())) {
            $this->context->addViolationAt('brancheOccurrence', $constraint->invalidBranchOccurrenceMessage, array(), null);
        }

        // Validate ProducerAttendance
        $producerAttendance = $this->objectManager
                ->getRepository('IsicsOpenMiamMiamBundle:ProducerAttendance')
                ->findOneBy(array(
                    'producer' => $attendance->getProducer(),
                    'branchOccurrence' => $attendance->getBranchOccurrence()
                ));

        if ($producerAttendance != $attendance->getProducerAttendance()) {
            $this->context->addViolationAt('brancheOccurrence', $constraint->invalidProducerAttendanceMessage, array(), null);
        }
    }
}
