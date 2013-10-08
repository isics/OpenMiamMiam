<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder;

use Doctrine\ORM\EntityManager;

class Statistics
{
    /**
     * @var EntityManager $entityManager;
     */
    protected $entityManager;

    /**
     * @var array $salesOrders
     */
    protected $salesOrders;

    /**
     * @var float $total
     */
    protected $total;

    /**
     * @var float $leftToPay
     */
    protected $leftToPay;

    /**
     * @var float $paidAmount
     */
    protected $paidAmount;

    /**
     * @var bool $isGlobalsStatisticsComputed
     */
    protected $isGlobalsStatisticsComputed;

    /**
     * @var bool $isPaymentStatisticsComputed
     */
    protected $isPaymentStatisticsComputed;

    /**
     * @var array $payment Statistics
     */
    protected $paymentStatistics;



    /**
     * Constructs object
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->isGlobalsStatisticsComputed = false;
        $this->isPaymentStatisticsComputed = false;
    }

    /**
     * Returns sales orders
     *
     * @return array
     */
    public function getSalesOrders()
    {
        return $this->salesOrders;
    }

    /**
     * Sets sales orders
     *
     * @param array $salesOrders
     */
    public function setSalesOrders(array $salesOrders)
    {
        $this->salesOrders = $salesOrders;

        $this->isGlobalsStatisticsComputed = false;
        $this->isPaymentStatisticsComputed = false;
    }

    /**
     * Computes payment statistics
     */
    public function computePaymentStatistics()
    {
        $this->paymentStatistics = $repository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Payment')
                ->getStatisticsForSalesOrders($this->salesOrders);

        $this->isPaymentStatisticsComputed = true;
    }

    /**
     * Computes globals stats
     */
    public function computeGlobalsStatistics()
    {
        if (!is_array($this->salesOrders)) {
            throw new \InvalidArgumentException('No sales orders to process');
        }

        $this->total = $this->leftToPay = $this->paidAmount = 0;
        foreach ($this->salesOrders as $salesOrder) {
            $this->total += $salesOrder->getTotal();
            $this->leftToPay += $salesOrder->getLeftToPay();
            $this->paidAmount += $salesOrder->getPaidAmount();
        }

        $this->isGlobalsStatisticsComputed = true;
    }

    /**
     * Returns total
     *
     * @return float
     */
    public function getTotal()
    {
        if (!$this->isGlobalsStatisticsComputed) {
            $this->computeGlobalsStatistics();
        }

        return $this->total;
    }

    /**
     * Returns left to pay
     *
     * @return float
     */
    public function getLeftToPay()
    {
        if (!$this->isGlobalsStatisticsComputed) {
            $this->computeGlobalsStatistics();
        }

        return $this->leftToPay;
    }

    /**
     * Returns paid amount
     *
     * @return float
     */
    public function getPaidAmount()
    {
        if (!$this->isGlobalsStatisticsComputed) {
            $this->computeGlobalsStatistics();
        }

        return $this->paidAmount;
    }

    /**
     * Returns payment statistics
     *
     * @return array
     */
    public function getPaymentStatistics()
    {
        if (!$this->isPaymentStatisticsComputed) {
            $this->computePaymentStatistics();
        }

        return $this->paymentStatistics;
    }
}
