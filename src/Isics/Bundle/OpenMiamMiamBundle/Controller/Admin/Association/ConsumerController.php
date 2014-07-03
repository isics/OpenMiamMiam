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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\PaymentAllocation;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Comment;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\CommentType;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConsumerController extends BaseController
{
    /**
     * @param Association $association
     * @param User        $consumer
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function secureConsumer(Association $association, User $consumer)
    {
        if (null === $this->get('open_miam_miam.consumer_manager')->getSubscription($association, $consumer)) {
            throw $this->createNotFoundException('Invalid consumer for association');
        }
    }

    /**
     * Secure sales order
     *
     * @param Association $association
     * @param SalesOrder $salesOrder
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function secureSalesOrder(Association $association, SalesOrder $salesOrder, User $consumer = null)
    {
        if ($salesOrder->getBranchOccurrence()->getBranch()->getAssociation() != $association) {
            throw $this->createNotFoundException('Invalid sales order for association');
        }

        if ($salesOrder->getUser() !== null && $salesOrder->getUser() != $consumer) {
            throw $this->createNotFoundException('Invalid sales order for consumer');
        }
    }

    /**
     * @param Association $association
     * @param Comment     $comment
     * @param User        $consumer
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function secureComment(Association $association, Comment $comment, User $consumer = null)
    {
        $error = $this->createNotFoundException('Invalid comment for consumer and association');

        if ($association->getId() !== $comment->getAssociation()->getId()) {
            throw $error;
        }

        if (null !== $consumer && $consumer->getId() !== $comment->getUser()->getId()) {
            throw $error;
        }
    }

    /**
     * Show a consumer
     *
     * @ParamConverter("association", class="IsicsOpenMiamMiamBundle:Association", options={"mapping": {"associationId": "id"}})
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     *
     * @param Association $association
     * @param User        $consumer
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function showAction(Association $association, User $consumer = null)
    {
        $this->secure($association);

        if (null !== $consumer) {
            $this->secureConsumer($association, $consumer);
        }

        $historySalesOrders = $this
            ->get('open_miam_miam.handler.association_sales_order_search')
            ->generateQueryBuilder($association, $consumer, 3)
            ->getQuery()
            ->getResult();

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:show.html.twig', array(
            'association'       => $association,
            'consumer'          => $consumer,
            'historySalesOrder' => $historySalesOrders
        ));
    }

    /**
     * @ParamConverter("association", class="IsicsOpenMiamMiamBundle:Association", options={"mapping": {"associationId": "id"}})
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     * @param User $consumer
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listSalesOrdersAction(Request $request, Association $association, User $consumer = null)
    {
        $this->secure($association);

        if (null != $consumer) {
            $this->secureConsumer($association, $consumer);
        }

        $handler = $this->get('open_miam_miam.handler.association_sales_order_search');
        $form = $handler->createSearchForm($association);
        $queryBuilder = $handler->generateQueryBuilder($association, $consumer);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            $handler->applyFormFilters($data, $queryBuilder);
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($queryBuilder->getQuery()));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.association.pagination.sales_orders'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:listSalesOrders.html.twig', array(
            'association' => $association,
            'consumer'    => $consumer,
            'salesOrders' => $pagerfanta,
            'form'        => $form->createView(),
        ));
    }

    /**
     * List a consumer's comments
     *
     * @ParamConverter("association", class="IsicsOpenMiamMiamBundle:Association", options={"mapping": {"associationId": "id"}})
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     * @ParamConverter("salesOrder", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping":{"salesOrderId": "id"}})
     *
     * @param Association $association
     * @param User        $consumer
     * @param SalesOrder  $salesOrder
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listCommentsAction(Request $request, Association $association, User $consumer = null, SalesOrder $salesOrder = null)
    {
        $this->secure($association);

        if (null !== $consumer) {
            $this->secureConsumer($association, $consumer);
        }

        if (null !== $salesOrder) {
            $this->secureSalesOrder($association, $salesOrder, $consumer);
        }

        $comments = $this->get('open_miam_miam.comment_manager')->getNotProcessedCommentsForAssociationConsumer(
            $association,
            $consumer,
            $salesOrder
        );

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:listComments.html.twig', array(
            'association' => $association,
            'consumer'    => $consumer,
            'comments'    => $comments,
            'salesOrder'  => $salesOrder
        ));
    }

    /**
     * Add a comment on a consumer
     *
     * @ParamConverter("association", class="IsicsOpenMiamMiamBundle:Association", options={"mapping": {"associationId": "id"}})
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     * @ParamConverter("salesOrder", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping":{"salesOrderId": "id"}})
     *
     * @param Request     $request
     * @param Association $association
     * @param User        $consumer
     * @param SalesOrder  $salesOrder
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function addCommentAction(Request $request, Association $association, User $consumer = null, SalesOrder $salesOrder = null)
    {
        $this->secure($association);

        if (null !== $consumer) {
            $this->secureConsumer($association, $consumer);
        }

        if (null !== $salesOrder) {
            $this->secureSalesOrder($association,$salesOrder, $consumer);
        }

        $comment = $this->get('open_miam_miam.comment_manager')->createComment(
            $association,
            $this->get('security.context')->getToken()->getUser(),
            $consumer,
            $salesOrder
        );

        $form = $this->createForm(new CommentType, $comment);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($comment);
                $em->flush();

                if ($request->isXmlHttpRequest()) {
                    return new Response('', 204);
                }

                return $this->redirect($request->headers->get('referer'));
            } else {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse($form->getErrorsAsString(), 400);
                }
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:addComment.html.twig', array(
            'association' => $association,
            'consumer'    => $consumer,
            'form'        => $form->createView(),
            'salesOrder'  => $salesOrder
        ));
    }

    /**
     * Process a comment
     *
     * @ParamConverter("association", class="IsicsOpenMiamMiamBundle:Association", options={"mapping": {"associationId": "id"}})
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     * @ParamConverter("comment", class="IsicsOpenMiamMiamBundle:Comment", options={"mapping": {"commentId": "id"}})
     *
     * @param Request     $request
     * @param Association $association
     * @param Comment     $comment
     * @param User        $consumer
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function processCommentAction(Request $request, Association $association, Comment $comment, User $consumer = null)
    {
        $this->secure($association);

        if (null !== $consumer) {
            $this->secureConsumer($association, $consumer);
        }

        $this->secureComment($association, $comment, $consumer);

        $em = $this->getDoctrine()->getManager();
        $comment->setIsProcessed(true);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    /**
     * List consumers
     *
     * @param Request     $request
     * @param Association $association
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listAction(Request $request, Association $association)
    {
        $this->secure($association);
        $handler = $this->get('open_miam_miam.handler.association_consumer');
        $form = $handler->createSearchForm();
        $qb = $handler->generateQueryBuilder($association);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $handler->applyFormFilters($qb, $data);
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb->getQuery()));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.association.pagination.consumers'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:list.html.twig', array(
            'association'   => $association,
            'subscriptions' => $pagerfanta,
            'form'          => $form->createView()
        ));
    }

    /**
     * List anonymous payments
     *
     * @param Request     $request
     * @param Association $association
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return Response
     */
    public function listAnonymousPaymentsAction(Request $request, Association $association)
    {
        $this->secure($association);

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Payment')
                ->getForConsumerAndAssociationQueryBuilder($association)

                ->getQuery()
        ));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.association.pagination.consumer_payments'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:listPayments.html.twig', array(
            'association' => $association,
            'payments'    => $pagerfanta
        ));
    }

    /**
     * List consumer payments
     *
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     *
     * @param Request     $request
     * @param Association $association
     * @param User        $consumer
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return Response
     */
    public function listPaymentsAction(Request $request, Association $association, User $consumer)
    {
        $this->secure($association);
        $this->secureConsumer($association, $consumer);

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Payment')
                ->getForConsumerAndAssociationQueryBuilder($association, $consumer)
                ->getQuery()
        ));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.association.pagination.consumer_payments'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:listPayments.html.twig', array(
            'association' => $association,
            'consumer'    => $consumer,
            'payments'    => $pagerfanta
        ));
    }

    /**
     * List allocations for payment
     *
     * @ParamConverter("payment", class="IsicsOpenMiamMiamBundle:Payment", options={"mapping": {"paymentId": "id"}})
     *
     * @param Association $association Association
     * @param Payment     $payment     Payment
     *
     * @return Response
     */
    public function listPaymentAllocationsAction(Association $association, Payment $payment)
    {
        $this->secure($association);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:listPaymentAllocations.html.twig', array(
            'association' => $association,
            'payment'     => $payment,
            'allocations' => $payment->getPaymentAllocations(),
            'consumer'    => $payment->getUser()
        ));
    }
}
