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
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderController extends BaseController
{
    /**
     * @param Producer $producer
     * @param BranchOccurrence $branchOccurrence
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function secureBranchOccurrence(Producer $producer, BranchOccurrence $branchOccurrence)
    {
        if (!$producer->hasBranch($branchOccurrence->getBranch())) {
            throw $this->createNotFoundException('Invalid branch for producer');
        }
    }

    /**
     * @param Producer $producer
     * @param SalesOrder $order
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureSalesOrder(Producer $producer, SalesOrder $order)
    {
        if (!$producer->hasBranch($order->getBranchOccurrence()->getBranch())) {
            throw $this->createNotFoundException('Invalid order for producer');
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

            throw new $this->createNotFoundException('Invalid sales order row for producer');
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
     * @param Request $request
     * @param Producer $producer
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function editAction(Request $request, Producer $producer)
    {
        $this->secure($producer);

        $order = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->findOneWithRows(
            $request->attributes->get('salesOrderId')
        );

        if (null === $order) {
            throw $this->createNotFoundException('No sales order found');
        }

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
                $user = $this->get('security.context')->getToken()->getUser();

                $this->get('open_miam_miam.sales_order_manager')->save(
                    $order,
                    $producer,
                    $user
                );

                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
                    $order->getUser(),
                    $order->getBranchOccurrence()->getBranch()->getAssociation()
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
     * Deletes a sales order
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
        $user = $this->get('security.context')->getToken()->getUser();

        $this->get('open_miam_miam.sales_order_manager')->deleteSalesOrderRow(
            $row,
            $user
        );

        $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
            $order->getUser(),
            $order->getBranchOccurrence()->getBranch()->getAssociation()
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
                $user = $this->get('security.context')->getToken()->getUser();

                $salesOrderManager = $this->get('open_miam_miam.sales_order_manager');
                $salesOrderManager->addRows($order, $data['products']->toArray(), $data['artificialProduct']);
                $salesOrderManager->save(
                    $order,
                    $producer,
                    $user
                );

                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
                    $order->getUser(),
                    $order->getBranchOccurrence()->getBranch()->getAssociation()
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

    /**
     * Get producer sales orders PDF for branch occurrence
     *
     * @ParamConverter("branchOccurrence", class="IsicsOpenMiamMiamBundle:BranchOccurrence", options={"mapping": {"branchOccurrenceId": "id"}})
     *
     * @param Producer $producer
     * @param BranchOccurrence $branchOccurrence
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function getSalesOrdersPdfForBranchOccurrenceAction(Producer $producer, BranchOccurrence $branchOccurrence)
    {
        $this->secure($producer);
        $this->secureBranchOccurrence($producer, $branchOccurrence);

        $salesOrdersPdf = $this->get('open_miam_miam.producer_sales_orders_pdf');

        $salesOrdersPdf->setSalesOrders(
            $this->get('open_miam_miam.producer_sales_order_manager')->getForBranchOccurrence($producer, $branchOccurrence)
        );

        return new StreamedResponse(function() use ($salesOrdersPdf){
            $salesOrdersPdf->render();
        });
    }

    /**
     * Get products to prepare PDF for branch occurrence
     *
     * @ParamConverter("branchOccurrence", class="IsicsOpenMiamMiamBundle:BranchOccurrence", options={"mapping": {"branchOccurrenceId": "id"}})
     *
     * @param Producer $producer
     * @param BranchOccurrence $branchOccurrence
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function getProductsToPreparePdfForBranchOccurrenceAction(Producer $producer, BranchOccurrence $branchOccurrence)
    {
        $this->secure($producer);
        $this->secureBranchOccurrence($producer, $branchOccurrence);

        $productsToPreparePdf = $this->get('open_miam_miam.products_to_prepare_pdf');

        $productsToPreparePdf->setProducerSalesOrders(
            $this->get('open_miam_miam.producer_sales_order_manager')->getForBranchOccurrence(
                $producer,
                $branchOccurrence
            )
        );

        return new StreamedResponse(function() use ($productsToPreparePdf){
            $productsToPreparePdf->render();
        });
    }
}
