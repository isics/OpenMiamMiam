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
            $adminResource = $adminResources[0];

            switch ($adminResource->getType()) {
                case AdminResource::TYPE_SUPER_ADMIN:
                    return $this->redirect('');
                case AdminResource::TYPE_ASSOCIATION:
                    return $this->redirect('');
                case AdminResource::TYPE_PRODUCER:
                    return $this->redirect($this->generateUrl(
                        'open_miam_miam.admin.producer.dashboard',
                        array('id' => $adminResource->getEntity()->getId())
                    ));
                case AdminResource::TYPE_RELAY:
                    return $this->redirect('');
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin:index.html.twig');
    }
}
