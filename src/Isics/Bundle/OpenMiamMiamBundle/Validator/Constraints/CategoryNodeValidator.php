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
use Isics\Bundle\OpenMiamMiamBundle\Model\Category\CategoryNode as _CategoryNode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CategoryNodeValidator extends ConstraintValidator
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
     * @param CategoryNode $categoryNode
     * @param Constraint   $constraint
     */
    public function validate($categoryNode, Constraint $constraint)
    {
        // Target required for 4 reference positions
        if (_CategoryNode::POSITION_FIRST_CHILD !== $categoryNode->getPosition()
            && _CategoryNode::POSITION_LAST_CHILD !== $categoryNode->getPosition()
            && null === $categoryNode->getTarget()) {

            $this->context->addViolationAt('target', 'error.required');
        }
    }
}
