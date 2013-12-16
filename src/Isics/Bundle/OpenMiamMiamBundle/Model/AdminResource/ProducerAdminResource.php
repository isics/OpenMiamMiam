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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

/**
 * Class ProducerAdminResource
 */
class ProducerAdminResource extends BaseEntityAdminResource
{
    /**
     * Constructor
     *
     * @param Producer $producer Producer
     */
    public function __construct(Producer $producer)
    {
        $this->entity = $producer;
    }

    /**
     * @see AdminResourceInterface
     */
    public function getType()
    {
        return 'producer';
    }
}
