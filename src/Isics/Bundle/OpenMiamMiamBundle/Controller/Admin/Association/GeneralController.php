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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $branches = $this->get('open_miam_miam.dashboard.association.tiles_builder')
            ->buildForAssociation($association);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association:showDashboard.html.twig', array(
            'association' => $association,
            'branches'    => $branches
        ));
    }

    /**
     * Edits association informations
     *
     * @param Request     $request
     * @param Association $association
     *
     * @return Response
     */
    public function editAction(Request $request, Association $association)
    {
        $this->secure($association);

        // @todo Replace all new types by a call to service
        $associationManager = $this->get('open_miam_miam.association_manager');
        $form            = $this->createForm(
            $this->get('open_miam_miam.form.type.association'),
            $association,
            array(
                'action' => $this->generateUrl('open_miam_miam.admin.association.edit', array('id' => $association->getId())),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $associationManager->save($association, $this->get('security.context')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.infos.message.updated');

                return $this->redirect($this->generateUrl('open_miam_miam.admin.association.edit', array('id' => $association->getId())));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association:edit.html.twig', array(
            'form'        => $form->createView(),
            'association' => $association,
            'activities'  => $associationManager->getActivities($association),
        ));
    }

    /**
     * @param Request     $request
     * @param Association $association
     *
     * @return Response
     */
    public function statisticsAction(Request $request, Association $association)
    {
        $form = $this->createForm(
            'open_miam_miam_association_statistics',
            null,
            array(
                'association' => $association
            )
        );

        $data = null;

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $this->get('open_miam_miam.handler.association_statistics')
                    ->getData($association, $form->getData());

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse($data->toArray());
                }
            } elseif ($request->isXmlHttpRequest()) {
                if ($request->isXmlHttpRequest()) {
                    return new Response(
                        $this->get('translator')->trans('admin.association.dashboard.statistics.form_errors'),
                        '400'
                    );
                }
            }
        }

        return $this->render('@IsicsOpenMiamMiam/Admin/Association/statistics.html.twig', array(
            'association' => $association,
            'form'        => $form->createView(),
            'data'        => $data
        ));
    }
}
