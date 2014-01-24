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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

            throw $this->createNotFoundException('Invalid sales order row for association');
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
     * Create sales order for branch occurrence
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
    public function createAction(Association $association, BranchOccurrence $branchOccurrence)
    {
        $this->secure($association);
        $this->secureBranchOccurrence($association, $branchOccurrence);

        $salesOrderManager = $this->get('open_miam_miam.sales_order_manager');

        $order = $salesOrderManager->createForBranchOccurrence($branchOccurrence);
        $salesOrderManager->save($order, $association, $this->get('security.context')->getToken()->getUser());

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.sales_order.edit',
            array('id' => $association->getId(), 'salesOrderId' => $order->getId())
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
                $this->get('open_miam_miam.sales_order_manager')->save($order, $association, $user);
                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit($association, $order->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.edit',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                ));
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:editionFormFields.html.twig', array(
                'association' => $association,
                'order' => $order,
                'form' => $form->createView()
            ));
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

        if ($order->getBranchOccurrence()->getBranch()->getAssociation()->getId() != $association->getId()
                || $order->getId() !== $row->getSalesOrder()->getId()) {
            throw new $this->createNotFoundException('Invalid sales order row for association');
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $this->get('open_miam_miam.sales_order_manager')->deleteSalesOrderRow($row, $user);
        $this->get('open_miam_miam.payment_manager')->computeConsumerCredit($association, $order->getUser());

        $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.updated');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.sales_order.edit',
            array('id' => $association->getId(), 'salesOrderId' => $order->getId())
        ));
    }

    /**
     * Add product to sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     * @ParamConverter("product", class="IsicsOpenMiamMiamBundle:Product", options={"mapping": {"productId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     * @param SalesOrder $order
     * @param Product $product
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function addProductAction(Request $request, Association $association, SalesOrder $order, Product $product)
    {
        $this->secure($association);
        $this->secureSalesOrder($association, $order);

        $availability = $this->get('open_miam_miam.branch_occurrence_manager')
                ->getProductAvailability($order->getBranchOccurrence(), $product);

        if (!$product->getProducer()->hasAssociation($association) || !$availability->isAvailable()) {
            throw $this->createNotFoundException('Invalid product');
        }

        $this->get('open_miam_miam.sales_order_manager')
                ->addProduct($order, $product, $association, $this->get('security.context')->getToken()->getUser());

        // todo: use salesOrderManager to do that
        $this->get('open_miam_miam.payment_manager')->computeConsumerCredit($association, $order->getUser());

        if ($request->isXmlHttpRequest()) {
            return $this->render(
                'IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:productToAdd.html.twig',
                array(
                    'association' => $association,
                    'order' => $order,
                    'product' => $product
                )
            );
        }

        $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.product_added');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.sales_order.edit',
            array('id' => $association->getId(), 'salesOrderId' => $order->getId())
        ));
    }

    /**
     * Add products to sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     * @param SalesOrder $order
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function addProductsAction(Request $request, Association $association, SalesOrder $order)
    {
        $this->secure($association);
        $this->secureSalesOrder($association, $order);

        $filterForm = $this->createForm(
            $this->get('open_miam_miam.form.type.products_filter'),
            null,
            array(
                'association' => $association,
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.add_products',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                ),
                'method' => 'POST'
            )
        );

        $artificialProductForm = $this->createForm(
            $this->get('open_miam_miam.form.type.artificial_product'),
            $this->get('open_miam_miam.product_manager')->createArtificialProduct(),
            array(
                'association' => $association,
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.add_products',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                ),
                'method' => 'POST'
            )
        );

        $filters = null;
        if ($request->isMethod('POST')) {
            $artificialProductForm->handleRequest($request);
            if ($artificialProductForm->isValid()) {
                $this->get('open_miam_miam.sales_order_manager')->addArtificialProduct(
                    $order,
                    $artificialProductForm->getData(),
                    $association,
                    $this->get('security.context')->getToken()->getUser()
                );

                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit($association, $order->getUser());

                if ($request->isXmlHttpRequest()) {
                    return $this->render(
                        'IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:artificialProductFormFields.html.twig',
                        array('artificialProductForm' => $artificialProductForm->createView())
                    );
                }

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.product_added');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.sales_order.edit',
                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                ));
            } elseif ($artificialProductForm->isSubmitted() && $request->isXmlHttpRequest()) {
                return $this->render(
                    'IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:artificialProductFormFields.html.twig',
                    array('artificialProductForm' => $artificialProductForm->createView()),
                    new Response('Failed', 400)
                );
            }

            $filterForm->handleRequest($request);
            if ($filterForm->isValid()) {
                $filters = $filterForm->getData();
            }
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $this->get('open_miam_miam.product_manager')->findForAssociationQuery($association, $filters)
        ));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        if ($filterForm->isSubmitted() && $request->isXmlHttpRequest()) {
            return $this->render(
                'IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:productsToAdd.html.twig',
                array(
                    'association' => $association,
                    'order'       => $order,
                    'products'    => $pagerfanta
                )
            );
        }

        return $this->render(
            'IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:addProducts.html.twig',
            array(
                'association' => $association,
                'order' => $order,
                'artificialProductForm' => $artificialProductForm->createView(),
                'filterForm' => $filterForm->createView(),
                'products' => $pagerfanta
            )
        );
    }

    /**
     * Pays a sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     * @param SalesOrder $order
     *
     * @return Response
     */
//    public function payAction(Request $request, Association $association, SalesOrder $order)
//    {
//        $this->secure($association);
//        $this->secureSalesOrder($association, $order);
//
//        $paymentManager = $this->get('open_miam_miam.payment_manager');
//
//        $form = $this->createForm(
//            $this->get('open_miam_miam.form.type.payment_allocation'),
//            $paymentManager->createPaymentAllocation($order),
//            array(
//                'action' => $this->generateUrl(
//                    'open_miam_miam.admin.association.sales_order.pay',
//                    array('id' => $association->getId(), 'salesOrderId' => $order->getId())
//                ),
//                'method' => 'POST'
//            )
//        );
//
//        if ($request->isMethod('POST')) {
//            $form->handleRequest($request);
//            if ($form->isValid()) {
//                $paymentManager->addPaymentAllocation(
//                    $order,
//                    $form->getData(),
//                    $this->get('security.context')->getToken()->getUser()
//                );
//
//                $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.payment_added');
//
//                return $this->redirect($this->generateUrl(
//                    'open_miam_miam.admin.association.sales_order.list_for_branch_occurrence',
//                    array('id' => $association->getId(), 'branchOccurrenceId' => $order->getBranchOccurrence()->getId())
//                ));
//            }
//        }
//
//        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:pay.html.twig', array(
//            'association' => $association,
//            'salesOrder' => $order,
//            'paymentsToAllocate' => $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Payment')->findToAllocatedForUser($order->getUser()),
//            'form' => $form->createView()
//        ));
//    }

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
//    public function deletePaymentAllocationAction(Association $association, SalesOrder $order, PaymentAllocation $paymentAllocation)
//    {
//        $this->secure($association);
//        $this->secureSalesOrder($association, $order);
//
//        if ($paymentAllocation->getSalesOrder()->getId() !== $order->getId()) {
//            throw $this->createNotFoundException('Invalid payment allocation for sales order');
//        }
//
//        $this->get('open_miam_miam.payment_manager')->deletePaymentAllocation(
//            $paymentAllocation,
//            $this->get('security.context')->getToken()->getUser()
//        );
//
//        $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.payment_allocation_deleted');
//
//        return $this->redirect($this->generateUrl(
//            'open_miam_miam.admin.association.sales_order.pay',
//            array('id' => $association->getId(), 'salesOrderId' => $order->getId())
//        ));
//    }

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
//    public function allocatePaymentAction(Association $association, SalesOrder $order, Payment $payment)
//    {
//        $this->secure($association);
//        $this->secureSalesOrder($association, $order);
//
//        if ($payment->getRest() == 0) {
//            throw $this->createNotFoundException('No rest for payment');
//        }
//        if ($order->getLeftToPay() == 0) {
//            throw $this->createNotFoundException('Order is settled');
//        }
//
//        $this->get('open_miam_miam.payment_manager')->allocatePayment(
//            $payment,
//            $order,
//            $this->get('security.context')->getToken()->getUser()
//        );
//
//        $this->get('session')->getFlashBag()->add('notice', 'admin.association.sales_orders.message.payment_allocated');
//
//        return $this->redirect($this->generateUrl(
//            'open_miam_miam.admin.association.sales_order.pay',
//            array('id' => $association->getId(), 'salesOrderId' => $order->getId())
//        ));
//    }

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

    /**
     * Export association deposit/withdrawal
     *
     * @ParamConverter("branchOccurrence", class="IsicsOpenMiamMiamBundle:BranchOccurrence", options={"mapping": {"branchOccurrenceId": "id"}})
     *
     * @param Association $association
     * @param BranchOccurrence $branchOccurrence
     *
     * @return Response
     */
    public function exportAction(Association $association, BranchOccurrence $branchOccurrence)
    {
        $this->secure($association);
        $this->secureBranchOccurrence($association, $branchOccurrence);

        $producerTransfer = $this->get('open_miam_miam.association_manager')
            ->getProducerTransferForBranchOccurrence($branchOccurrence);

        $document = $this->get('open_miam_miam.association.deposit_withdrawal');

        $filename = $this->get('translator')->trans('excel.association.sales_orders.deposit_withdrawal.filename', array(
            '%branch%' => $branchOccurrence->getBranch()->getSlug(),
            '%year%'   => $branchOccurrence->getEnd()->format('Y'),
            '%day%'    => $branchOccurrence->getEnd()->format('d'),
            '%month%'  => $branchOccurrence->getEnd()->format('m')
        ));

        $response = new StreamedResponse();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment;filename="%s"', $filename));

        $response->setCallback(function() use ($document, $producerTransfer, $branchOccurrence) {
            $document->generate($producerTransfer, $branchOccurrence);

            $writer = new \PHPExcel_Writer_Excel2007($document->getExcel());

            $writer->save('php://output');
        });

        return $response;
    }

    /**
     * Show and manage user payments form
     *
     * @ParamConverter("user", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"userId": "id"}})
     *
     * @param Request     $request     Request
     * @param Association $association Association
     * @param User        $user        User
     *
     * @return Response
     */
    public function manageUserPaymentsAction(Request $request, Association $association, User $user)
    {
        $allocatePayment = $this->get('open_miam_miam.factory.allocate_payment')->create($association, $user);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.allocate_payment'),
            $allocatePayment
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->get('open_miam_miam.allocate_payment_manager')
                        ->process($form->getData());
                }
                catch (\Exception $e) {
                    die('OOPS :)');
                }
            }
        }

        return $this->render(
            'IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:manageUserPayments.html.twig',
            array(
                'association' => $association,
                'form'        => $form->createView()
            )
        );
    }
}
