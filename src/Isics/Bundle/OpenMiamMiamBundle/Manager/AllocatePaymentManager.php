<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\PaymentAllocation;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Model\Association\AllocatePayment;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class AllocatePaymentManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PaymentManager
     */
    private $paymentManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, PaymentManager $paymentManager)
    {
        $this->entityManager  = $entityManager;
        $this->paymentManager = $paymentManager;
    }

    /**
     * Save user (or anonymous) payments allocation
     *
     * @param AllocatePayment $allocatePayment
     */
    public function process(AllocatePayment $allocatePayment)
    {
        // First, we persist new payments (if possible)
        $payments = $allocatePayment->getPayments();
        foreach ($payments as $index => $payment) {
            if (null === $payment->getId()) {
                if (0 != $payment->getAmount()) {
                    $this->entityManager->persist($payment);
                }
                else {
                    unset($payments[$index]);
                }
            }
        }

        // Then, we orders salesOrders by date descendent
        $salesOrders = $allocatePayment->getSalesOrders();

        $this->allocatePaymentsToSalesOrders($payments, $salesOrders, $allocatePayment->getUser());

        $this->entityManager->flush();

        // Compute
        $this->paymentManager->computeConsumerCredit(
            $allocatePayment->getAssociation(),
            $allocatePayment->getUser()
        );
    }

    /**
     * Allocate given payments to given sales orders
     *
     * @param array $payments
     * @param array $salesOrders
     */
    public function allocatePaymentsToSalesOrders(array $payments, array $salesOrders, User $user = null)
    {
        // Sort payments
        usort($payments, function($payment1, $payment2) {
            $p1Date = $payment1->getDate();
            $p2Date = $payment2->getDate();

            if ($p1Date > $p2Date) {
                return 1;
            }
            elseif ($p1Date < $p2Date) {
                return -1;
            }
            else {
                return 0;
            }
        });

        // Sort sales orders
        usort($salesOrders, function($salesOrder1, $salesOrder2) {
            $so1Date = $salesOrder1->getDate();
            $so2Date = $salesOrder2->getDate();

            if ($so1Date > $so2Date) {
                return 1;
            }
            elseif ($so1Date < $so2Date) {
                return -1;
            }
            else {
                return 0;
            }
        });

        foreach ($salesOrders as $salesOrder) {
            foreach ($payments as $indexPayment => $payment) {
                $this->paymentManager->allocatePayment($payment, $salesOrder, $user);

                // If payments has fully been allocated, we remove it from stack
                if (0 == $payment->getRest()) {
                    unset($payments[$indexPayment]);
                }

                // Il sales order is fully settled, we remove it from stack and go to next salesOrder
                if (0 == $salesOrder->getCredit()) {
                    break;
                }
            }
        }
    }

    /**
     * Delete payment and all related allocations
     *
     * @param Payment $payment
     */
    public function deletePaymentAndAllocations(Payment $payment)
    {
        foreach ($payment->getPaymentAllocations() as $paymentAllocation) {
            $this->deletePaymentAllocation($paymentAllocation);
        }

        $this->paymentManager->delete($payment);
    }

    /**
     * Delete a payment's allocation
     *
     * @param PaymentAllocation $paymentAllocation Payment's allocation
     */
    public function deletePaymentAllocation(PaymentAllocation $paymentAllocation, $withFlush = true)
    {
        $salesOrder = $paymentAllocation->getSalesOrder();
        $payment = $paymentAllocation->getPayment();

        $salesOrder->setCredit($salesOrder->getCredit() - $paymentAllocation->getAmount());
        $payment->setRest($payment->getRest() + $paymentAllocation->getAmount());

        $this->entityManager->persist($salesOrder);
        $this->entityManager->persist($payment);

        $this->entityManager->remove($paymentAllocation);

        if ($withFlush) {
            $this->entityManager->flush();
        }
    }
}
