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

class UserSwitchController extends Controller
{
    /**
     * Shows users
     *
     * @return Response
     */
    public function listAction()
    {
        $users = $this->get('doctrine')->getRepository('IsicsOpenMiamMiamUserBundle:User')->findWithACE();

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\UserSwitch:list.html.twig', array(
            'users' => $users,
        ));
    }
}
