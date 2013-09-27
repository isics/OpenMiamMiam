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
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\Admin\ProducerSalesOrderType;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class SalesOrderController extends BaseController
{
    /**
     * List sales order
     *
     * @param Producer $producer
     *
     * @return Response
     */
    public function listAction(Producer $producer)
    {
        $this->secure($producer);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:list.html.twig', array(
            'producer' => $producer,
            'salesOrders' => $this->get('open_miam_miam.producer_sales_order_manager')->getForNextBranchOccurrences($producer)
        ));
    }

    /**
     * Update a sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Request $request
     * @param Producer $producer
     * @param SalesOrder $order
     *
     * @return Response
     */
    public function editAction(Request $request, Producer $producer, SalesOrder $order)
    {
        $this->secure($producer);

        $producerSalesOrder = new ProducerSalesOrder($producer, $order);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.sales_order'),
            new ProducerSalesOrder($producer, $order),
            array(
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.producer.sales_order.edit',
                    array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
                ),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->get('open_miam_miam.sales_order_manager')->save(
                    $order,
                    $producer,
                    $this->get('security.context')->getToken()->getUser()
                );

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.producer.sales_order.edit',
                    array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:edit.html.twig', array(
            'producer' => $producer,
            'producerSalesOrder' => $producerSalesOrder,
            'form' => $form->createView(),
            'activities' => $this->get('open_miam_miam.producer_sales_order_manager')->getActivities($producerSalesOrder)
        ));
    }
}
