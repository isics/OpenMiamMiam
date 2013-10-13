<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Super;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GeneralController extends Controller
{
    /**
     * Show Dashboard
     *
     * @return Response
     */
    public function showDashboardAction()
    {
        $activities = $this->getDoctrine()
            ->getRepository('IsicsOpenMiamMiamBundle:Activity')
            ->findBy(array(), array('date' => 'desc'));

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super:showDashboard.html.twig', array(
            'activities' => $activities,
        ));
    }
}
