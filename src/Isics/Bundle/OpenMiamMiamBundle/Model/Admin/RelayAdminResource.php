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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Relay;

/**
 * Class RelayAdminResource
 */
class RelayAdminResource extends EntityAdminResource
{
    /**
     * Constructor
     *
     * @param Relay $relay Relay
     */
    public function __construct(Relay $relay)
    {
        $this->entity = $relay;
    }

    /**
     * @see AdminResourceInterface
     */
    public function getType()
    {
        return 'relay';
    }
}
