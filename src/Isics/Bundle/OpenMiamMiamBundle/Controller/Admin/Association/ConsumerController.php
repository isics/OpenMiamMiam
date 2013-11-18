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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\PaymentAllocation;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class ConsumerController extends BaseController
{
    /**
     * @param Association $association
     * @param User $consumer
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
     * @param Association $association
     * @param User $consumer
     * @param Payment $payment
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function securePayment(Association $association, User $consumer, Payment $payment)
    {
        $this->secureConsumer($association, $consumer);
        if ($payment->getAssociation()->getId() != $association->getId()
                || $payment->getUser()->getId() != $consumer->getId()) {
            throw $this->createNotFoundException('Invalid payment for association');
        }
    }

    /**
     * List consumers
     *
     * @param Request $request
     * @param Association $association
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listAction(Request $request, Association $association)
    {
        $this->secure($association);

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Subscription')
                    ->getForAssociationQueryBuilder($association)
                    ->getQuery()
        ));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.association.pagination.consumers'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:list.html.twig', array(
            'association'=> $association,
            'subscriptions' => $pagerfanta
        ));
    }

    /**
     * List anonymous payments
     *
     * @param Request $request
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
        } catch(NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:listPayments.html.twig', array(
            'association'=> $association,
            'payments' => $pagerfanta
        ));
    }

    /**
     * List consumer payments
     *
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     * @param User $consumer
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
        } catch(NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:listPayments.html.twig', array(
            'association'=> $association,
            'consumer' => $consumer,
            'payments' => $pagerfanta
        ));
    }

    /**
     * Create consumer payment
     *
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
    public function createPaymentAction(Request $request, Association $association, User $consumer)
    {
        $this->secure($association);
        $this->secureConsumer($association, $consumer);

        $paymentManager = $this->get('open_miam_miam.payment_manager');

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.payment'),
            $paymentManager->createPayment($consumer, $association),
            array(
                'without_amount' => false,
                'validation_groups' => array('without_allocation'),
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.association.consumer.create_payment',
                    array('id' => $association->getId(), 'consumerId' => $consumer->getId())
                ),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $paymentManager->save($form->getData());

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.consumers.message.payment_created');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.consumer.list_payments',
                    array('id' => $association->getId(), 'consumerId' => $consumer->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:createPayment.html.twig', array(
            'association'=> $association,
            'consumer' => $consumer,
            'form' => $form->createView()
        ));
    }

    /**
     * Deletes consumer payment
     *
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     * @ParamConverter("payment", class="IsicsOpenMiamMiamBundle:Payment", options={"mapping": {"paymentId": "id"}})
     *
     * @param Association $association
     * @param User $consumer
     * @param Payment $payment
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function deletePaymentAction(Association $association, User $consumer, Payment $payment)
    {
        $this->secure($association);
        $this->securePayment($association, $consumer, $payment);

        $this->get('open_miam_miam.payment_manager')->delete($payment);

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.consumers.message.payment_deleted');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.consumer.list_payments',
            array('id' => $association->getId(), 'consumerId' => $consumer->getId())
        ));
    }

    /**
     * Edits consumer payment
     *
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     * @ParamConverter("payment", class="IsicsOpenMiamMiamBundle:Payment", options={"mapping": {"paymentId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     * @param User $consumer
     * @param Payment $payment
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function editPaymentAction(Request $request, Association $association, User $consumer, Payment $payment)
    {
        $this->secure($association);
        $this->securePayment($association, $consumer, $payment);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.payment'),
            $payment,
            array(
                'without_amount' => false,
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.association.consumer.edit_payment',
                    array('id' => $association->getId(), 'consumerId' => $consumer->getId(), 'paymentId' => $payment->getId())
                ),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->get('open_miam_miam.payment_manager')->save($form->getData());

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.consumers.message.payment_updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.consumer.list_payments',
                    array('id' => $association->getId(), 'consumerId' => $consumer->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:editPayment.html.twig', array(
            'association'=> $association,
            'consumer' => $consumer,
            'payment' => $payment,
            'form' => $form->createView(),
            'salesOrders' => $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->findNotSettledForUserAndAssociation(
                $consumer,
                $association
            )
        ));
    }

    /**
     * Delete payment allocation to sales order
     *
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     * @ParamConverter("paymentAllocation", class="IsicsOpenMiamMiamBundle:PaymentAllocation", options={"mapping": {"paymentAllocationId": "id"}})
     *
     * @param Association $association
     * @param User $consumer
     * @param PaymentAllocation $paymentAllocation
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function deletePaymentAllocationAction(Association $association, User $consumer, PaymentAllocation $paymentAllocation)
    {
        $this->secure($association);
        $this->secureConsumer($association, $consumer);

        $payment = $paymentAllocation->getPayment();
        if ($payment->getUser()->getId() !== $consumer->getId()) {
            throw $this->createNotFoundException('Invalid payment allocation for consumer');
        }

        $this->get('open_miam_miam.payment_manager')->deletePaymentAllocation(
            $paymentAllocation,
            $this->get('security.context')->getToken()->getUser()
        );

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.consumers.message.payment_allocation_deleted');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.consumer.edit_payment',
            array('id' => $association->getId(), 'consumerId' => $consumer->getId(), 'paymentId' => $payment->getId())
        ));
    }

    /**
     * Allocate payment to sales order
     *
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     * @ParamConverter("order", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     * @ParamConverter("payment", class="IsicsOpenMiamMiamBundle:Payment", options={"mapping": {"paymentId": "id"}})
     *
     * @param Association $association
     * @param User $consumer
     * @param Payment $payment
     * @param SalesOrder $order
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function allocateSalesOrderAction(Association $association, User $consumer, Payment $payment, SalesOrder $order)
    {
        $this->secure($association);
        $this->secureConsumer($association, $consumer);

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

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.consumers.message.sales_order_allocated');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.consumer.edit_payment',
            array('id' => $association->getId(), 'consumerId' => $consumer->getId(), 'paymentId' => $payment->getId())
        ));
    }
}
