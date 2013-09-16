<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Admin;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;

/**
 * Class AssociationAdminResource
 */
class AssociationAdminResource extends EntityAdminResource
{
    /**
     * Constructor
     *
     * @param Association $association Association
     */
    public function __construct(Association $association)
    {
        $this->entity = $association;
    }

    /**
     * @see AdminResourceInterface
     */
    public function getType()
    {
        return 'association';
    }
}
