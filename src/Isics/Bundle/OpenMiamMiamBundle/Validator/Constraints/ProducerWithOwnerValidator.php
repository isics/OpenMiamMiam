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

class ProducerWithOwnerValidator extends ConstraintValidator
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
     * @param ProducerWithOwner $producerWithOwner
     * @param Constraint        $constraint
     */
    public function validate($producerWithOwner, Constraint $constraint)
    {
        // User exists
        if (null === $this->entityManager
            ->getRepository('IsicsOpenMiamMiamUserBundle:User')
            ->findOneByEmail($producerWithOwner->getOwnerEmail())) {
            $this->context->addViolationAt('ownerEmail', 'error.user_not_found');
        }
    }
}
