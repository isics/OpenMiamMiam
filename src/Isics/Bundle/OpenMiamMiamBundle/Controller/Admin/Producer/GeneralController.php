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
use Symfony\Component\HttpFoundation\Request;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\Admin\ProducerType;

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

        $attendancesManager = $this->get('open_miam_miam.producer_attendances_manager');
        $producerSalesOrderManager = $this->get('open_miam_miam.producer_sales_order_manager');

        $salesOrders = $producerSalesOrderManager->getForNextBranchOccurrences($producer);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer:showDashboard.html.twig', array(
            'producer'              => $producer,
            'nbUnknownAttendances'  => $attendancesManager->getNbUnknownAttendances($attendancesManager->getNextAttendancesOf($producer)),
            'nbOutOfStockProducts'  => $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Product')->countOutOfStockProductsForProducer($producer),
            'nbSalesOrderToPrepare' => $salesOrders->countSalesOrders()
        ));
    }

    /**
     * Edits producer informations
     *
     * @param Request $request
     * @param Producer $producer
     *
     * @return Response
     */
    public function editAction(Request $request, Producer $producer)
    {
        $this->secure($producer);

        // @todo Replace all new types by a call to service
        $producerManager = $this->get('open_miam_miam.producer_manager');
        $form = $this->createForm(
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
                $producerManager->save($producer);

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.infos.message.updated');

                return $this->redirect($this->generateUrl('open_miam_miam.admin.producer.edit', array('id' => $producer->getId())));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer:edit.html.twig', array(
            'form' => $form->createView(),
            'producer' => $producer
        ));
    }
}
