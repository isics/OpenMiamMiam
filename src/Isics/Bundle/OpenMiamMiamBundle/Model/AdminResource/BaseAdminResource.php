<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\AdminResource;

/**
 * Class BaseAdminResource
 */
abstract class BaseAdminResource implements AdminResourceInterface
{
    /**
     * @var boolean $ownerPerspective
     */
    protected $ownerPerspective = false;

    /**
     * Defines resource owner perspective
     *
     * @param boolean $ownerPerspective
     */
    public function setOwnerPerspective($ownerPerspective)
    {
        $this->ownerPerspective = $ownerPerspective;
    }

    /**
     * Returns true if resource is for owner perspective
     *
     * @return boolean
     */
    public function isOwnerPerspective()
    {
        return $this->ownerPerspective;
    }
}