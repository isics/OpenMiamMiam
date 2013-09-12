<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Producer;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseController extends Controller
{
    /**
     * Secures an action
     *
     * @param Producer $producer
     *
     * @throws AccessDeniedException
     */
    protected function secure(Producer $producer)
    {
        if (false === $this->get('security.context')->isGranted('OPERATOR', $producer)) {
            throw new AccessDeniedException();
        }
    }
}
