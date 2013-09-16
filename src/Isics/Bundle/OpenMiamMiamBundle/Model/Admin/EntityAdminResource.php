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

/**
 * Class EntityAdminResource
 */
abstract class EntityAdminResource implements AdminResourceInterface
{
    /**
     * @var Entity $entity
     */
    protected $entity;

    /**
     * Constructor
     *
     * @param mixed $entity Entity
     */
    public function __construct(mixed $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Returns Entity
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
