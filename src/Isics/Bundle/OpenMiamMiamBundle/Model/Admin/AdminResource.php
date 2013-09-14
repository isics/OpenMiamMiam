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
 * Class AdminResource
 */
class AdminResource
{
    const TYPE_SUPER_ADMIN = 0;
    const TYPE_ASSOCIATION = 1;
    const TYPE_PRODUCER    = 2;
    const TYPE_RELAY       = 3;

    /**
     * @var mixed $resource
     */
    protected $type;

    /**
     * @var mixed $entity
     */
    protected $entity;

    /**
     * Contructs object
     *
     * @param integer $type   Type
     * @param mixed   $entity Entity (optional)
     */
    public function __construct($type, $entity = null)
    {
        $this->type   = $type;
        $this->entity = $entity;
    }

    /**
     * Returns type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns Entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
