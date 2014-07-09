<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin;

use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResource;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GeneralController extends Controller
{
    /**
     * Displays administrations choices or redirect according to user credentials
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $adminManager     = $this->get('open_miam_miam.admin_manager');
        $adminResources   = $adminManager->findAvailableAdminResources();
        $nbAdminResources = count($adminResources);

        if (0 === $nbAdminResources) {
            throw new AccessDeniedException();
        }

        if (1 === $nbAdminResources) {
            $adminMenu = $this->get('open_miam_miam.menu.admin');

            return $this->redirect($adminMenu->getFirstChild()->getUri());
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin:index.html.twig');
    }

    /**
     * Proxy method to go to switch user page (avoid securityChecker order bug)
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function goToUserSwitchAction()
    {
        return $this->redirect($this->generateUrl('open_miam_miam.admin.super.user_switch.list'));
    }
}
