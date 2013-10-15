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
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\PaymentAllocation;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\Statistics;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderController extends BaseController
{
    /**
     * @param Association $association
     * @param BranchOccurrence $branchOccurrence
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureBranchOccurrence(Association $association, BranchOccurrence $branchOccurrence)
    {
        if ($association->getId() !== $branchOccurrence->getBranch()->getAssociation()->getId()) {
            throw $this->createNotFoundException('Invalid branch occurrence for association');
        }
    }

    /**
     * @param Association $association
     * @param SalesOrder $order
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureSalesOrder(Association $association, SalesOrder $order)
    {
        if ($order->getBranchOccurrence()->getBranch()->getAssociation()->getId() != $association->getId()) {
            throw $this->createNotFoundException('Invalid order for association');
        }
    }

    /**
     * @param Association $association
     * @param SalesOrder $order
     * @param SalesOrderRow $row
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureSalesOrderRow(Association $association, SalesOrder $order, SalesOrderRow $row)
    {
        if ($order->getBranchOccurrence()->getBranch()->getAssociation()->getId() != $association->getId()
                || $order->getId() !== $row->getSalesOrder()->getId()) {

            throw new $this->createNotFoundException('Invalid sales order row for association');
        }
    }

    /**
     * List sales orders
     *
     * @param Association $association
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listAction(Association $association)
    {
        $this->secure($association);

        $branchOccurrenceManager = $this->get('open_miam_miam.branch_occurrence_manager');
        $nextBranchOccurrence = $branchOccurrenceManager->getNextNotClosedForAssociation($association);
        if (null === $nextBranchOccurrence) {
            return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:noBranchOccurrence.html.twig', array(
                'association' => $association
            ));
        }

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.sales_order.list_for_branch_occurrence',
            array('id' => $association->getId(), 'branchOccurrenceId' => $nextBranchOccurrence->getId())
        ));
    }

    /**
     * List sales orders for branch occurrence
     *
     * @ParamConverter("branchOccurrence", class="IsicsOpenMiamMiamBundle:BranchOccurrence", options={"mapping": {"branchOccurrenceId": "id"}})
     *
     * @param Association $association
     * @param BranchOccurrence $branchOccurrence
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listForBranchOccurrenceAction(Association $association, BranchOccurrence $branchOccurrence)
    {
        $this->secure($association);
        $this->secureBranchOccurrence($association, $branchOccurrence);

        $salesOrders = $this->get('open_miam_miam.sales_order_manager')->getForBranchOccurrence($branchOccurrence);

        $statistics = $this->get('open_miam_miam.sales_order_statistics');
        $statistics->setSalesOrders($salesOrders);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:list.html.twig', array(
            'association' => $association,
            'branchOccurrence' => $branchOccurrence,
            'branchOccurrences' => $this->get('open_miam_miam.branch_occurrence_manager')->getToProcessForAssociation($association),
            'salesOrders' => $salesOrders,
            'salesOrdersStats' => $statistics
        ));
    }

    /**
     * Update a sales order
     *
     * @param Request $request
     * @param Association $association
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function editAction(Request $request, Association $association)
    {
        $this->secure($association);

        $order = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->findOneWithRows(
            $request->attributes->get('salesOrderId')
        );

        if (null === $order) {
            throw $this->createNotFoundException('No sales order found');
        }

        $this->secureSalesOrder($association, $order);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.sales_order'),
            $order,
            array(
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.edit',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
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
                    $association,
                    $user
                );

                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
                    $user,
                    $association
                );

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.edit',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:edit.html.twig', array(
            'association' => $association,
            'order' => $order,
            'form' => $form->createView(),
            'activities' => $this->get('open_miam_miam.sales_order_manager')->getActivities($order)
        ));
    }

    /**
     * Deletes a sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     * @ParamConverter("row", class="IsicsOpenMiamMiamBundle:SalesOrderRow", options={"mapping": {"salesOrderRowId": "id"}})
     *
     * @param Association $association
     * @param SalesOrder $order
     * @param SalesOrderRow $row
     *
     * @throws
     *
     * @return Response
     */
    public function deleteSalesOrderRowAction(Association $association, SalesOrder $order, SalesOrderRow $row)
    {
        $this->secure($association);
        $this->secureSalesOrderRow($association, $order, $row);

        $user = $this->get('security.context')->getToken()->getUser();
        $this->get('open_miam_miam.sales_order_manager')->deleteSalesOrderRow(
            $row,
            $user
        );

        $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
            $user,
            $association
        );

        $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.updated');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.sales_order.edit',
            array('id' => $association->getId(), 'salesOrderId' => $order->getId())
        ));
    }

    /**
     * Add rows for a sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     * @param SalesOrder $order
     *
     * @return Response
     */
    public function addSalesOrderRowsAction(Request $request, Association $association, SalesOrder $order)
    {
        $this->secure($association);
        $this->secureSalesOrder($association, $order);

        $productManager = $this->get('open_miam_miam.product_manager');
        $artificialProduct = $productManager->createArtificialProduct();

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.add_rows_sales_order'),
            array('artificialProduct' => $artificialProduct),
            array(
                'salesOrder' => $order,
                'association' => $association,
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.add_rows',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
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
                    $association,
                    $user
                );

                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
                    $user,
                    $association
                );

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.edit',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:addSalesOrderRows.html.twig', array(
            'association' => $association,
            'salesOrder' => $order,
            'form' => $form->createView()
        ));
    }

    /**
     * Update a sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     * @param SalesOrder $order
     *
     * @return Response
     */
    public function payAction(Request $request, Association $association, SalesOrder $order)
    {
        $this->secure($association);
        $this->secureSalesOrder($association, $order);

        $paymentManager = $this->get('open_miam_miam.payment_manager');

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.payment_allocation'),
            $paymentManager->createPaymentAllocation($order),
            array(
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.pay',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                ),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $paymentManager->addPaymentAllocation(
                    $order,
                    $form->getData(),
                    $this->get('security.context')->getToken()->getUser()
                );

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.payment_added');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.list_for_branch_occurrence',
                    array('id' => $association->getId(), 'branchOccurrenceId' => $order->getBranchOccurrence()->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:pay.html.twig', array(
            'association' => $association,
            'salesOrder' => $order,
            'paymentsToAllocate' => $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Payment')->findToAllocatedForUser($order->getUser()),
            'form' => $form->createView()
        ));
    }

    /**
     * Delete payment allocation to sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     * @ParamConverter("paymentAllocation", class="IsicsOpenMiamMiamBundle:PaymentAllocation", options={"mapping": {"paymentAllocationId": "id"}})
     *
     * @param Association $association
     * @param SalesOrder $order
     * @param PaymentAllocation $paymentAllocation
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function deletePaymentAllocationAction(Association $association, SalesOrder $order, PaymentAllocation $paymentAllocation)
    {
        $this->secure($association);
        $this->secureSalesOrder($association, $order);

        if ($paymentAllocation->getSalesOrder()->getId() !== $order->getId()) {
            throw $this->createNotFoundException('Invalid payment allocation for sales order');
        }

        $this->get('open_miam_miam.payment_manager')->deletePaymentAllocation(
            $paymentAllocation,
            $this->get('security.context')->getToken()->getUser()
        );

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.payment_allocation_deleted');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.sales_order.pay',
            array('id' => $association->getId(), 'salesOrderId' => $order->getId())
        ));
    }

    /**
     * Allocate payment to sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     * @ParamConverter("payment", class="IsicsOpenMiamMiamBundle:Payment", options={"mapping": {"paymentId": "id"}})
     *
     * @param Association $association
     * @param SalesOrder $order
     * @param Payment $payment
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function allocatePaymentAction(Association $association, SalesOrder $order, Payment $payment)
    {
        $this->secure($association);
        $this->secureSalesOrder($association, $order);

        if ($payment->getRest() == 0) {
            throw $this->createNotFoundException('No rest for payment');
        }
        if ($order->getLeftToPay() == 0) {
            throw $this->createNotFoundException('Order is settled');
        }

        $this->get('open_miam_miam.payment_manager')->allocatePayment(
            $payment,
            $order,
            $this->get('security.context')->getToken()->getUser()
        );

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.payment_allocated');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.sales_order.pay',
            array('id' => $association->getId(), 'salesOrderId' => $order->getId())
        ));
    }

    /**
     * Get sales orders PDF for branch occurrence
     *
     * @ParamConverter("branchOccurrence", class="IsicsOpenMiamMiamBundle:BranchOccurrence", options={"mapping": {"branchOccurrenceId": "id"}})
     *
     * @param Association $association
     * @param BranchOccurrence $branchOccurrence
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function getSalesOrdersPdfForBranchOccurrenceAction(Association $association, BranchOccurrence $branchOccurrence)
    {
        $this->secure($association);
        $this->secureBranchOccurrence($association, $branchOccurrence);

        $salesOrdersPdf = $this->get('open_miam_miam.sales_orders_pdf');
        $salesOrdersPdf->setSalesOrders(
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->findWithRowsForBranchOccurrence($branchOccurrence)
        );

        return new StreamedResponse(function() use ($salesOrdersPdf){
            $salesOrdersPdf->render();
        });
    }
}
