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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrder;
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

            throw $this->createNotFoundException('Invalid sales order row for producer');
        }
    }

    /**
     * Ensure sales order is open to ad product or edit content
     *
     * @param SalesOrder $salesOrder
     *
     * @throw NotFoundHttpException
     */
    public function ensureSalesOrderIsOpen(SalesOrder $salesOrder)
    {
        if ($this->get('open_miam_miam.sales_order_manager')->isLocked($salesOrder)) {
            throw $this->createNotFoundException('Sales order is locked');
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
            'salesOrders' => $this->get('open_miam_miam.producer_sales_order_manager')->getForNextBranchOccurrences($producer),
            'historySalesOrders' => $this->get('open_miam_miam.producer_sales_order_manager')->getForLastBranchOccurrences($producer)
        ));
    }

    public function listSalesOrderHistoryAction(Request $request, Producer $producer)
    {
        $this->secure($producer);

        $handler = $this->get('open_miam_miam.handler.producer_sales_order_history');
        $form = $handler->createSearchForm($producer);
        $qb = $handler->generateQueryBuilder($producer);

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.association.pagination.consumers'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:listSalesOrderHistory.html.twig', array(
            'producer' => $producer,
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

        // Should we have to remove form controls ?
        $isLocked = $this->get('open_miam_miam.sales_order_manager')->isLocked($order);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.producer_sales_order'),
            new ProducerSalesOrder($producer, $order),
            array(
                'locked' => $isLocked,
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.producer.sales_order.edit',
                    array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
                ),
                'method' => 'POST'
            )
        );

        if (!$isLocked && $request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user = $this->get('security.context')->getToken()->getUser();

                $this->get('open_miam_miam.sales_order_manager')->save(
                    $order,
                    $producer,
                    $user
                );

                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
                    $order->getBranchOccurrence()->getBranch()->getAssociation(),
                    $order->getUser()
                );

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.producer.sales_order.edit',
                    array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
                ));
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:editionFormFields.html.twig', array(
                'producer' => $producer,
                'producerSalesOrder' => $producerSalesOrder,
                'form' => $form->createView(),
                'mustRemoveFormControls' => $isLocked
            ));
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:edit.html.twig', array(
            'producer' => $producer,
            'producerSalesOrder' => $producerSalesOrder,
            'form' => $form->createView(),
            'activities' => $this->get('open_miam_miam.producer_sales_order_manager')->getActivities($producerSalesOrder),
            'mustRemoveFormControls' => $isLocked
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
        $this->ensureSalesOrderIsOpen($order);

        $order = $row->getSalesOrder();
        $user = $this->get('security.context')->getToken()->getUser();

        $this->get('open_miam_miam.sales_order_manager')->deleteSalesOrderRow(
            $row,
            $user
        );

        $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
            $order->getBranchOccurrence()->getBranch()->getAssociation(),
            $order->getUser()
        );

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.updated');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.producer.sales_order.edit',
            array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
        ));
    }

    /**
     * Add product to sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     * @ParamConverter("product", class="IsicsOpenMiamMiamBundle:Product", options={"mapping": {"productId": "id"}})
     *
     * @param Request $request
     * @param Producer $producer
     * @param SalesOrder $order
     * @param Product $product
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function addProductAction(Request $request, Producer $producer, SalesOrder $order, Product $product)
    {
        $this->secure($producer);
        $this->secureSalesOrder($producer, $order);
        $this->ensureSalesOrderIsOpen($order);

        $availability = $this->get('open_miam_miam.branch_occurrence_manager')
                ->getProductAvailability($order->getBranchOccurrence(), $product);

        if ($product->getProducer()->getId() != $producer->getId() || !$availability->isAvailable()) {
            throw $this->createNotFoundException('Invalid product');
        }

        $this->get('open_miam_miam.sales_order_manager')
                ->addProduct($order, $product, $producer, $this->get('security.context')->getToken()->getUser());

        // todo: use salesOrderManager to do that
        $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
            $order->getBranchOccurrence()->getBranch()->getAssociation(),
            $order->getUser()
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render(
                'IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:productToAdd.html.twig',
                array(
                    'producer' => $producer,
                    'order' => $order,
                    'product' => $product
                )
            );
        }

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.product_added');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.producer.sales_order.edit',
            array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
        ));
    }

    /**
     * Add products to sales order
     *
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Request $request
     * @param Producer $producer
     * @param SalesOrder $order
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function addProductsAction(Request $request, Producer $producer, SalesOrder $order)
    {
        $this->secure($producer);
        $this->secureSalesOrder($producer, $order);
        $this->ensureSalesOrderIsOpen($order);

        $filterForm = $this->createForm(
            $this->get('open_miam_miam.form.type.products_filter'),
            null,
            array(
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.producer.sales_order.add_products',
                    array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
                ),
                'method' => 'POST'
            )
        );

        $artificialProductForm = $this->createForm(
            $this->get('open_miam_miam.form.type.artificial_product'),
            $this->get('open_miam_miam.product_manager')->createArtificialProduct($producer),
            array(
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.producer.sales_order.add_products',
                    array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
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
                    $producer,
                    $this->get('security.context')->getToken()->getUser()
                );

                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
                    $order->getBranchOccurrence()->getBranch()->getAssociation(),
                    $order->getUser()
                );

                if ($request->isXmlHttpRequest()) {
                    return $this->render(
                        'IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:artificialProductFormFields.html.twig',
                        array('artificialProductForm' => $artificialProductForm->createView())
                    );
                }

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.sales_orders.message.product_added');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.producer.sales_order.edit',
                    array('id' => $producer->getId(), 'salesOrderId' => $order->getId())
                ));
            } elseif ($artificialProductForm->isSubmitted() && $request->isXmlHttpRequest()) {
                return $this->render(
                    'IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:artificialProductFormFields.html.twig',
                    array('artificialProductForm' => $artificialProductForm->createView()),
                    new Response('Failed', 400)
                );
            }

            $filterForm->handleRequest($request);
            if ($filterForm->isValid()) {
                $filters = $filterForm->getData();
            }
        }

        $products = $this->get('open_miam_miam.product_manager')->findForProducer($producer, $filters);

        if ($filterForm->isSubmitted() && $request->isXmlHttpRequest()) {
            return $this->render(
                'IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:productsToAdd.html.twig',
                array(
                    'producer' => $producer,
                    'order' => $order,
                    'products' => $products
                )
            );
        }

        return $this->render(
            'IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:addProducts.html.twig',
            array(
                'producer' => $producer,
                'order' => $order,
                'artificialProductForm' => $artificialProductForm->createView(),
                'filterForm' => $filterForm->createView(),
                'products' => $products
            )
        );
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
