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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Comment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\PaymentAllocation;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\ArtificialProductType;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\CommentType;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\ProductsFilterType;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\SalesOrderType;
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
        $salesOrderManager->save($order, $association, $this->get('security.token_storage')->getToken()->getUser());

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

        $form = $this->container->get('form.factory')
            ->createNamedBuilder(
                'open_miam_miam_sales_order',
                SalesOrderType::class,
                $order,
                array(
                    'action' => $this->generateUrl(
                        'open_miam_miam.admin.association.sales_order.edit',
                        array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                    ),
                    'method' => 'POST'
                )
            )
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user = $this->get('security.token_storage')->getToken()->getUser();
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

        $user = $this->get('security.token_storage')->getToken()->getUser();
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
                ->addProduct($order, $product, $association, $this->get('security.token_storage')->getToken()->getUser());

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

        $filterForm = $this->container->get('form.factory')
            ->createNamedBuilder(
                'open_miam_miam_products_filter_type',
                ProductsFilterType::class,
                null,
                array(
                    'association' => $association,
                    'action' => $this->generateUrl(
                        'open_miam_miam.admin.association.sales_order.add_products',
                        array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                    ),
                    'method' => 'POST'
                )
            )
            ->getForm();

        $artificialProductForm = $this->container->get('form.factory')
            ->createNamedBuilder(
                'open_miam_miam_artificial_product',
                ArtificialProductType::class,
                $this->get('open_miam_miam.product_manager')->createArtificialProduct(),
                array(
                    'association' => $association,
                    'action' => $this->generateUrl(
                        'open_miam_miam.admin.association.sales_order.add_products',
                        array('id' => $association->getId(), 'salesOrderId' => $order->getId())
                    ),
                    'method' => 'POST'
                )
            )
            ->getForm();

        $filters = null;
        if ($request->isMethod('POST')) {
            $artificialProductForm->handleRequest($request);
            if ($artificialProductForm->isValid()) {
                $this->get('open_miam_miam.sales_order_manager')->addArtificialProduct(
                    $order,
                    $artificialProductForm->getData(),
                    $association,
                    $this->get('security.token_storage')->getToken()->getUser()
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

        $filename = $this->get('translator')->trans(
            'pdf.association.sales_orders.filename',
            $branchOccurrence->getParamsForDocumentsName()
        );

        return new StreamedResponse(function() use ($salesOrdersPdf, $filename){
            $salesOrdersPdf->render($filename);
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
    public function getDepositWithdrawalExcelForBranchOccurrenceAction(Association $association, BranchOccurrence $branchOccurrence)
    {
        $this->secure($association);
        $this->secureBranchOccurrence($association, $branchOccurrence);

        $producerTransfer = $this->get('open_miam_miam.association_manager')
            ->getProducerTransferForBranchOccurrence($branchOccurrence);

        $document = $this->get('open_miam_miam.association.deposit_withdrawal');

        $filename = $this->get('translator')->trans(
            'excel.association.sales_orders.deposit_withdrawal.filename',
            $branchOccurrence->getParamsForDocumentsName()
        );

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
}
