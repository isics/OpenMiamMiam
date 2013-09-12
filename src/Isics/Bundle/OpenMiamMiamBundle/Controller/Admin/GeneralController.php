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

use Isics\Bundle\OpenMiamMiamBundle\Form\Type\Admin\AdminResourceChoiceType;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResourceCollection;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResourceInterface;
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
        $adminManager = $this->get('open_miam_miam.admin_manager');
        $adminResourceCollection = $adminManager->findAvailableAdminResources();
        $nbChoices = count($adminResourceCollection);

        if ($nbChoices == 0) {
            throw new AccessDeniedException();
        }

        if ($nbChoices == 1) {
            return $this->redirect($adminResourceCollection->getFirst()->getRoute());
        }

        $form = $this->getChoicesForm($adminResourceCollection);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $adminResource = $adminResourceCollection[$data['admin']];

                return $this->redirect($adminResource->getRoute());
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * Displays administrations choices
     *
     * @param mixed $object
     *
     * @throws \LogicException
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function chooseAreaAction($object)
    {
        $adminManager = $this->get('open_miam_miam.admin_manager');
        $adminResourceCollection = $adminManager->findAvailableAdminResources();
        if (count($adminResourceCollection) == 0) {
            throw new AccessDeniedException();
        }

        // Retrieve current admin resource from object
        $currentAdminResource = $adminResourceCollection->getByObject($object);
        if (null === $currentAdminResource) {
            throw new \LogicException('Unknown administration resource');
        }

        $form = $this->getChoicesForm($adminResourceCollection, $currentAdminResource);

        return $this->render('IsicsOpenMiamMiamBundle:Admin:chooseArea.html.twig', array('form' => $form->createView()));
    }

    /**
     * Returns administration areas choices form
     *
     * @param AdminResourceCollection $adminResourceCollection
     * @param AdminResourceInterface $adminResource
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getChoicesForm(AdminResourceCollection $adminResourceCollection, AdminResourceInterface $adminResource = null)
    {
        $adminResourceChoiceType = new AdminResourceChoiceType($adminResourceCollection);

        return $this->createForm(
            $adminResourceChoiceType,
            array('admin' => null === $adminResource ? null : $adminResourceCollection->getOffset($adminResource)),
            array('action' => $this->generateUrl('open_miam_miam.admin'), 'method' => 'POST')
        );
    }
}
