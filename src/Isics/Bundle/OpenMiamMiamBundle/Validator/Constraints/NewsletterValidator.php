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

class NewsletterValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     *
     * @param Newsletter $newsletter
     * @param Constraint $constraint
     */
    public function validate($newsletter, Constraint $constraint)
    {
        // If association's newsletter, ensure that branches are branches of the association
        if (null !== $association = $newsletter->getAssociation()) {
            foreach ($newsletter->getBranches() as $branch) {
                if ($association !== $branch->getAssociation()) {
                    $this->context->addViolationAt('branches', 'error.invalid');
                    break;
                }
            }
        }

        // Branches (included without_branch) not empty
        if (0 === count($newsletter->getBranches()) + ($newsletter->isWithoutBranch() ? 1 : 0)) {
            $this->context->addViolationAt('branches', 'error.invalid');
        }
    }
}
