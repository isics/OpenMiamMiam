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

use Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Producer\BaseController;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class CalendarController extends BaseController
{
    /**
     * Manages calendar
     *
     * @param Producer $producer
     *
     * @return Response
     */
    public function indexAction(Producer $producer)
    {
        $this->secure($producer);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\Calendar:index.html.twig', array());
    }
}
