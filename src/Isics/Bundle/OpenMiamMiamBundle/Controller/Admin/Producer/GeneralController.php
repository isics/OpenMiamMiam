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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GeneralController extends BaseController
{
    /**
     * Show Dashboard
     *
     * @param Producer $producer
     *
     * @return Response
     */
    public function showDashboardAction(Producer $producer)
    {
        $this->secure($producer);

        $tiles = $this->get('open_miam_miam.dashboard.producer.tiles_builder')
            ->buildForProducer($producer);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer:showDashboard.html.twig', array(
            'producer' => $producer,
            'tiles'    => $tiles
        ));
    }

    /**
     * Edits producer informations
     *
     * @param Request  $request
     * @param Producer $producer
     *
     * @return Response
     */
    public function editAction(Request $request, Producer $producer)
    {
        $this->secure($producer);

        // @todo Replace all new types by a call to service
        $producerManager = $this->get('open_miam_miam.producer_manager');
        $form            = $this->createForm(
            $this->get('open_miam_miam.form.type.producer'),
            $producer,
            array(
                'action' => $this->generateUrl('open_miam_miam.admin.producer.edit', array('id' => $producer->getId())),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $producerManager->save($producer, $this->get('security.context')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.infos.message.updated');

                return $this->redirect($this->generateUrl('open_miam_miam.admin.producer.edit', array('id' => $producer->getId())));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer:edit.html.twig', array(
            'form'     => $form->createView(),
            'producer' => $producer
        ));
    }

    /**
     * @param Request  $request
     * @param Producer $producer
     *
     * @return Response
     */
    public function statisticsAction(Request $request, Producer $producer)
    {
        $form = $this->createForm(
            'open_miam_miam_producer_statistics',
            null,
            array(
                'producer' => $producer
            )
        );

        $data = null;

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $this->get('open_miam_miam.handler.producer_statistics')
                    ->getData($producer, $form->getData());

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse($data->toArray());
                }
            } elseif ($request->isXmlHttpRequest()) {
                if ($request->isXmlHttpRequest()) {
                    return new Response(
                        $this->get('translator')->trans('admin.producer.dashboard.statistics.form_errors'),
                        '400'
                    );
                }
            }
        }

        return $this->render('@IsicsOpenMiamMiam/Admin/Producer/statistics.html.twig', array(
            'producer' => $producer,
            'form'     => $form->createView(),
            'data'     => $data
        ));
    }
}
