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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\Admin\AdminResourceChoiceType;
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
    public function chooseAdminAction(Request $request)
    {
        $adminManager = $this->get('open_miam_miam.admin_manager');
        $adminResourceCollection = $adminManager->findAvailableAdminResources();
        $nbChoices = count($adminResourceCollection);

        if ($nbChoices == 0) {
            throw new AccessDeniedException();
        }

        if ($nbChoices == 1) {
            return $this->redirect($adminResourceCollection->getFirst()->getRoute());
        }

        $adminResourceChoiceType = new AdminResourceChoiceType($adminResourceCollection);
        $form = $this->createForm(
            $adminResourceChoiceType,
            null,
            array('action' => $this->generateUrl('open_miam_miam.admin'), 'method' => 'POST')
        );
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $adminResource = $adminResourceCollection[$data['admin']];

                return $this->redirect($adminResource->getRoute());
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin:chooseAdmin.html.twig', array('form' => $form->createView()));
    }
}
