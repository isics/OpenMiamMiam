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
        $rootNodes = $this->entityManager
            ->getRepository('IsicsOpenMiamMiamBundle:Category')
            ->getRootNodes();

        $categoryId = $categoryNode->getCategory()->getId();

        // If not yet a root node or node is root, only first child position is allowed
        if ((empty($rootNodes) ||
            !empty($rootNodes) && null !== $categoryId && $rootNodes[0]->getId() === $categoryId)
            && _CategoryNode::POSITION_FIRST_CHILD !== $categoryNode->getPosition()) {

            $this->context->addViolationAt('position', 'error.tree.invalid_position');
        }

        // First child position is forbidden in other cases
        if (!empty($rootNodes)
            && (null === $categoryId || null !== $categoryId && $rootNodes[0]->getId() !== $categoryId)
            && _CategoryNode::POSITION_FIRST_CHILD === $categoryNode->getPosition()) {

            $this->context->addViolationAt('position', 'error.tree.invalid_position');
        }

        // Target required for 4 reference positions
        if (in_array($categoryNode->getPosition(), array(
                _CategoryNode::POSITION_FIRST_CHILD_OF, _CategoryNode::POSITION_LAST_CHILD_OF,
                _CategoryNode::POSITION_PREV_SIBLING_OF, _CategoryNode::POSITION_NEXT_SIBLING_OF
            )) && null === $categoryNode->getTarget()) {

            $this->context->addViolationAt('target', 'error.required');
        }

        // Not a sibling of root
        if (in_array($categoryNode->getPosition(), array(
                _CategoryNode::POSITION_PREV_SIBLING_OF, _CategoryNode::POSITION_NEXT_SIBLING_OF
            )) && 0 === $categoryNode->getTarget()->getLvl()) {

            $this->context->addViolationAt('target', 'error.tree.invalid_position');
        }

        // Depth limit
        if (in_array($categoryNode->getPosition(), array(
                _CategoryNode::POSITION_FIRST_CHILD_OF, _CategoryNode::POSITION_LAST_CHILD_OF
            )) && 1 < $categoryNode->getTarget()->getLvl()) {

            $this->context->addViolationAt('target', 'error.tree.too_deep');
        }

        // Category is not a parent of target
        if (null !== $categoryId && null !== $categoryNode->getTarget()) {
            $path = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:Category')
                ->getPath($categoryNode->getTarget());

            if (in_array($categoryNode->getCategory(), $path)) {
                $this->context->addViolationAt('target', 'error.tree.invalid_position');
            }
        }
    }
}
