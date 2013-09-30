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
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class SalesOrderController extends BaseController
{
    /**
     * @param Producer $producer
     * @param SalesOrder $order
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureSalesOrder(Producer $producer, SalesOrder $order)
    {
        if (!$producer->hasBranch($order->getBranchOccurrence()->getBranch())) {
            throw $this->createNotFoundException('Invalid branch for producer');
        }
    }

    /**
     * @param Producer $producer
     * @param SalesOrder $order
     * @param SalesOrderRow $row
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureSalesOrderRow(Producer $producer, SalesOrder $order, SalesOrderRow $row)
    {
        if (!$producer->hasBranch($order->getBranchOccurrence()->getBranch())
            || $row->getProducer()->getId() !== $producer->getId()
            || $order->getId() !== $row->getSalesOrder()->getId()) {

            throw new $this->createNotFoundException();
        }
    }

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
        $this->secureSalesOrder($producer, $order);

        $producerSalesOrder = new ProducerSalesOrder($producer, $order);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.producer_sales_order'),
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

    /**
     * Update a sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     * @ParamConverter("row", class="IsicsOpenMiamMiamBundle:SalesOrderRow", options={"mapping": {"salesOrderRowId": "id"}})
     *
     * @param Producer $producer
     * @param SalesOrder $order
     * @param SalesOrderRow $row
     *
     * @throws
     *
     * @return Response
     */
    public function deleteSalesOrderRowAction(Producer $producer, SalesOrder $order, SalesOrderRow $row)
    {
        $this->secure($producer);
        $this->secureSalesOrderRow($producer, $order, $row);

        $order = $row->getSalesOrder();
        $this->get('open_miam_miam.sales_order_manager')->deleteSalesOrderRow(
            $row,
            $producer,
            $this->get('security.context')->getToken()->getUser()
        );

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.updated');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.producer.sales_order.edit',
            array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
        ));
    }

    /**
     * Add rows for a sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Request $request
     * @param Producer $producer
     * @param SalesOrder $order
     *
     * @return Response
     */
    public function addSalesOrderRowsAction(Request $request, Producer $producer, SalesOrder $order)
    {
        $this->secure($producer);
        $this->secureSalesOrder($producer, $order);

        $productManager = $this->get('open_miam_miam.product_manager');
        $artificialProduct = $productManager->createArtificialProduct($producer);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.add_rows_sales_order'),
            array('artificialProduct' => $artificialProduct),
            array(
                'salesOrder' => $order,
                'producer' => $producer,
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.producer.sales_order.add_rows',
                    array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
                ),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                $salesOrderManager = $this->get('open_miam_miam.sales_order_manager');
                $salesOrderManager->addRows($order, $data['products']->toArray(), $data['artificialProduct']);
                $salesOrderManager->save(
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

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:addSalesOrderRows.html.twig', array(
            'producer' => $producer,
            'salesOrder' => $order,
            'form' => $form->createView()
        ));
    }
}
