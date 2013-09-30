<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association;

use Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association\BaseController;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\HttpFoundation\Request;

class GeneralController extends BaseController
{
    /**
     * Show Dashboard
     *
     * @param Association $association
     *
     * @return Response
     */
    public function showDashboardAction(Association $association)
    {
        $this->secure($association);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association:showDashboard.html.twig', array(
            'association'=> $association,
        ));
    }
}
