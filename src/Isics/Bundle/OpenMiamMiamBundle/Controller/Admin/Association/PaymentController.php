<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Model\Association\AllocatePayment;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends BaseController
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
     * Anonymous payments form
     *
     * @param Request     $request
     * @param Association $association
     * @param string|null $redirectRoute
     *
     * @return Response
     */
    public function manageAnonymousPaymentsAction(Request $request, Association $association, $redirectRoute = null)
    {
        $this->secure($association);

        if (null === $redirectRoute) {
            $redirectRoute = $this->generateUrl('open_miam_miam.admin.association.consumer.list', array(
                'id' => $association->getId()
            ));
        }

        $allocatePayment = $this->get('open_miam_miam.factory.allocate_payment')->create($association);
        $subscription    = $this->get('open_miam_miam.subscription_manager')->create($association);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.allocate_payment'),
            $allocatePayment
        );

        $response = new Response(null, 200);

        if ($request->isMethod('POST')) {
            if ($this->handlePaymentsAllocationForm($request, $form)) {
                return $this->redirect($redirectRoute);
            } else {
                $response->setStatusCode(400);
            }
        }

        return $this->render(
            'IsicsOpenMiamMiamBundle:Admin/Association/Payment:form.html.twig',
            array(
                'association'   => $association,
                'user'          => null,
                'subscription'  => $subscription,
                'form'          => $form->createView(),
                'redirectRoute' => $redirectRoute
            ),
            $response
        );
    }

    /**
     * User payments form
     *
     * @ParamConverter("user", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"userId": "id"}})
     *
     * @param Request     $request
     * @param Association $association
     * @param User        $user
     * @param null        $redirectRoute
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function manageUserPaymentsAction(Request $request, Association $association, User $user, $redirectRoute = null)
    {
        $this->secure($association);

        if (null === $redirectRoute) {
            $redirectRoute = $this->generateUrl('open_miam_miam.admin.association.consumer.list', array(
                'id' => $association->getId()
            ));
        }

        $allocatePayment = $this->get('open_miam_miam.factory.allocate_payment')->create($association, $user);
        $subscription    = $this->get('open_miam_miam.subscription_manager')->create($association, $user);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.allocate_payment'),
            $allocatePayment
        );

        $response = new Response(null, 200);

        if ($request->isMethod('POST')) {
            if ($this->handlePaymentsAllocationForm($request, $form)) {
                return $this->redirect($redirectRoute);
            } else {
                $response->setStatusCode(400);
            }
        }

        return $this->render(
            'IsicsOpenMiamMiamBundle:Admin/Association/Payment:form.html.twig',
            array(
                'association'   => $association,
                'user'          => $user,
                'subscription'  => $subscription,
                'form'          => $form->createView(),
                'redirectRoute' => $redirectRoute
            ),
            $response
        );
    }

    /**
     * Manage payments for a sales order
     *
     * @ParamConverter("salesOrder", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Request     $request     Request
     * @param Association $association Association
     * @param SalesOrder  $salesOrder  SalesOrder
     *
     * @return Response
     */
    public function manageSalesOrderPaymentsAction(Request $request, Association $association, SalesOrder $salesOrder)
    {
        $redirectRoute = $this->generateUrl('open_miam_miam.admin.association.sales_order.edit', array(
            'id'           => $association->getId(),
            'salesOrderId' => $salesOrder->getId()
        ));

        $user = $salesOrder->getUser();

        if (null === $user) {
            return $this->manageAnonymousPaymentsAction($request, $association, $redirectRoute);
        }

        return $this->manageUserPaymentsAction($request, $association, $user, $redirectRoute);
    }

    /**
     * Handle payments form
     *
     * @param Request         $request
     * @param Form            $form
     *
     * @return bool
     */
    protected function handlePaymentsAllocationForm(Request $request, Form $form)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $this->get('open_miam_miam.allocate_payment_manager')
                    ->process($form->getData());
              die('OK');

                return true;
            } catch (\Exception $e) {
            }
        }

        return false;
    }

    /**
     * Render anonymous summary for association
     *
     * @param Association $association
     *
     * @return Response
     */
    public function anonymousSummaryAction(Association $association)
    {
        $this->secure($association);

        $subscription          = $this->get('open_miam_miam.subscription_manager')->create($association);
        $hasMissingAllocations = $this->get('open_miam_miam.payment_manager')->hasMissingAllocations($association);

        return $this->render('IsicsOpenMiamMiamBundle:Admin/Association/Payment:userSummary.html.twig', array(
            'association'           => $association,
            'subscription'          => $subscription,
            'hasMissingAllocations' => $hasMissingAllocations
        ));
    }

    /**
     * Render user summary for association
     *
     * @ParamConverter("user", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"userId": "id"}})
     *
     * @param Association $association
     * @param User        $user
     *
     * @return Response
     */
    public function userSummaryAction(Association $association, User $user)
    {
        $this->secure($association);

        $subscription          = $this->get('open_miam_miam.subscription_manager')->create($association, $user);
        $hasMissingAllocations = $this->get('open_miam_miam.payment_manager')->hasMissingAllocations($association, $user);

        return $this->render('IsicsOpenMiamMiamBundle:Admin/Association/Payment:userSummary.html.twig', array(
            'association'           => $association,
            'user'                  => $user,
            'subscription'          => $subscription,
            'hasMissingAllocations' => $hasMissingAllocations
        ));
    }

    /**
     * Render sales order summary
     *
     * @ParamConverter("salesOrder", class="IsicsOpenMiamMiamBundle:SalesOrder", options={"mapping": {"salesOrderId": "id"}})
     *
     * @param Association $association
     * @param SalesOrder  $salesOrder
     *
     * @return Response
     */
    public function salesOrderSummaryAction(Association $association, SalesOrder $salesOrder)
    {
        $this->secure($association);

        $user = $salesOrder->getUser();

        $subscription          = $this->get('open_miam_miam.subscription_manager')->create($association, $user);
        $hasMissingAllocations = $this->get('open_miam_miam.payment_manager')->hasMissingAllocations($association, $user);

        return $this->render('IsicsOpenMiamMiamBundle:Admin/Association/Payment:salesOrderSummary.html.twig', array(
            'association'           => $association,
            'user'                  => $user,
            'subscription'          => $subscription,
            'order'                 => $salesOrder,
            'hasMissingAllocations' => $hasMissingAllocations
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
    public function deleteAction(Association $association, User $consumer, Payment $payment)
    {
        $this->secure($association);
        $this->securePayment($association, $consumer, $payment);

        $this->get('open_miam_miam.allocate_payment_manager')->deletePaymentAndAllocations($payment);

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.consumers.message.payment_deleted');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.consumer.list_payments',
            array('id' => $association->getId(), 'consumerId' => $consumer->getId())
        ));
    }
}