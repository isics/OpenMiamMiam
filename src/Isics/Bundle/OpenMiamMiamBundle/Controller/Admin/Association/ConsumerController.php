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
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')
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
            'consumers' => $pagerfanta
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
                    ->getForConsumerAndAssociationQueryBuilder($consumer, $association)
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
     * @param Request $request
     * @param Association $association
     * @param User $consumer
     * @param Payment $payment
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function deletePaymentAction(Request $request, Association $association, User $consumer, Payment $payment)
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
            'form' => $form->createView()
        ));
    }
}
