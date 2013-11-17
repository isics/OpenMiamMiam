<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\PaymentAllocation;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Subscription;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

/**
 * Class PaymentManager
 * Manager for payment
 */
class PaymentManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var ActivityManager $activityManager
     */
    protected $activityManager;



    /**
     * Constructs object
     *
     * @param EntityManager $entityManager
     * @param ActivityManager $activityManager
     */
    public function __construct(EntityManager $entityManager, ActivityManager $activityManager)
    {
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
    }

    /**
     * Returns a new payment
     *
     * @param User $user
     * @param Association $association
     *
     * @return Payment
     */
    public function createPayment(User $user, Association $association)
    {
        $payment = new Payment();
        $payment->setType(Payment::TYPE_CASH);
        $payment->setDate(new \DateTime());
        $payment->setUser($user);
        $payment->setAssociation($association);

        return $payment;
    }

    /**
     * Returns a new payment allocation for an order
     *
     * @param SalesOrder $order
     *
     * @return PaymentAllocation
     */
    public function createPaymentAllocation(SalesOrder $order)
    {
        $payment = new Payment();
        $payment->setType(Payment::TYPE_CASH);
        $payment->setDate(new \DateTime());
        $payment->setAmount($order->getLeftToPay());
        $payment->setRest($order->getLeftToPay());
        $payment->setUser($order->getUser());
        $payment->setAssociation($order->getBranchOccurrence()->getBranch()->getAssociation());

        $paymentAllocation = new PaymentAllocation();
        $paymentAllocation->setSalesOrder($order);
        $paymentAllocation->setDate(new \DateTime());
        $paymentAllocation->setAmount($order->getLeftToPay());

        $payment->addPaymentAllocation($paymentAllocation);

        return $paymentAllocation;
    }

    /**
     * Compute consumer credit
     *
     * @param Association $association
     * @param User $user
     */
    public function computeConsumerCredit(Association $association, User $user = null)
    {
        $subscription = $association->getSubscriptionForUser($user);

        if (null === $subscription) {
            $subscription = new Subscription();
            $subscription->setAssociation($association);
            $subscription->setUser($user);
        }

        $salesOrderTotal = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')
                ->getTotalForUserAndAssociation($association, $user);
        $paymentsAmount = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:Payment')
                ->getAmountForUserAndAssociation($association, $user);

        $subscription->setCredit($paymentsAmount-$salesOrderTotal);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();
    }

    /**
     * Add new payment allocation
     *
     * @param SalesOrder $order
     * @param PaymentAllocation $paymentAllocation
     * @param User $user
     */
    public function addPaymentAllocation(SalesOrder $order, PaymentAllocation $paymentAllocation, User $user)
    {
        $payment = $paymentAllocation->getPayment();
        $payment->setAmount($paymentAllocation->getAmount());
        $payment->setRest(0);

        $order->setCredit($order->getCredit()+$paymentAllocation->getAmount());

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        // Subscription
        $this->computeConsumerCredit($payment->getAssociation(), $order->getUser());

        // Activity
        $activity = $this->activityManager->createFromEntities(
            'activity_stream.sales_order.payment.added',
            array('%order_ref%' => $order->getRef(), '%amount%' => $this->activityManager->formatFloatNumber($paymentAllocation->getAmount())),
            $order,
            $order->getBranchOccurrence()->getBranch()->getAssociation(),
            $user
        );
        $this->entityManager->persist($activity);

        $this->entityManager->flush();
    }

    /**
     * Deletes payment allocation
     *
     * @param PaymentAllocation $paymentAllocation
     * @param User $user
     */
    public function deletePaymentAllocation(PaymentAllocation $paymentAllocation, User $user)
    {
        $order = $paymentAllocation->getSalesOrder();
        $payment = $paymentAllocation->getPayment();

        $payment->setRest($payment->getRest()+$paymentAllocation->getAmount());
        $order->setCredit($order->getCredit()-$paymentAllocation->getAmount());

        $this->entityManager->persist($payment);
        $this->entityManager->persist($order);
        $this->entityManager->remove($paymentAllocation);

        $activity = $this->activityManager->createFromEntities(
            'activity_stream.sales_order.payment.allocation_deleted',
            array('%order_ref%' => $order->getRef(), '%amount%' => $this->activityManager->formatFloatNumber($paymentAllocation->getAmount())),
            $order,
            $order->getBranchOccurrence()->getBranch()->getAssociation(),
            $user
        );
        $this->entityManager->persist($activity);

        $this->entityManager->flush();
    }

    /**
     * Allocates a payment to a sales order
     *
     * @param Payment $payment
     * @param SalesOrder $order
     * @param User $user
     *
     * @throws \LogicException
     */
    public function allocatePayment(Payment $payment, SalesOrder $order, User $user)
    {
        if ($payment->getRest() == 0) {
            throw new \LogicException('No rest for payment');
        }
        if ($order->getLeftToPay() == 0) {
            throw new \LogicException('Order is settled');
        }

        $amount = $order->getLeftToPay() > $payment->getRest() ? $payment->getRest() : $order->getLeftToPay();

        $paymentAllocation = new PaymentAllocation();
        $paymentAllocation->setSalesOrder($order);
        $paymentAllocation->setDate(new \DateTime());
        $paymentAllocation->setAmount($amount);

        $payment->addPaymentAllocation($paymentAllocation);
        $payment->setRest($payment->getRest()-$amount);

        $order->setCredit($order->getCredit()+$amount);

        $this->entityManager->persist($payment);
        $this->entityManager->persist($order);

        $activity = $this->activityManager->createFromEntities(
            'activity_stream.sales_order.payment.allocated',
            array('%order_ref%' => $order->getRef(), '%amount%' => $this->activityManager->formatFloatNumber($amount)),
            $order,
            $order->getBranchOccurrence()->getBranch()->getAssociation(),
            $user
        );
        $this->entityManager->persist($activity);

        $this->entityManager->flush();
    }

    /**
     * Saves payment
     *
     * @param Payment $payment
     */
    public function save(Payment $payment)
    {
        $payment->computeRest();

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        // Subscription
        $this->computeConsumerCredit($payment->getAssociation(), $payment->getUser());
    }

    /**
     * Deletes payment
     *
     * @param Payment $payment
     */
    public function delete(Payment $payment)
    {
        $this->entityManager->remove($payment);
        $this->entityManager->flush();

        // Subscription
        $this->computeConsumerCredit($payment->getAssociation(), $payment->getUser());
    }
}
