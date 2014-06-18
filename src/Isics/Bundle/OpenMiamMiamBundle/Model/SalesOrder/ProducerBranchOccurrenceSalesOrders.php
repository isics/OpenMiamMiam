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

use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrder;

class ProducerBranchOccurrenceSalesOrders
{
    /**
     * @var Producer
     */
    protected $producer;

    /**
     * @var BranchOccurrence
     */
    protected $branchOccurrence;

    /**
     * @var array
     */
    protected $salesOrders;



    /**
     * Constructs object
     *
     * @param Producer $producer
     * @param BranchOccurrence $branchOccurrence
     */
    public function __construct(Producer $producer, BranchOccurrence $branchOccurrence)
    {
        $this->producer = $producer;
        $this->branchOccurrence = $branchOccurrence;
        $this->salesOrders = array();
    }

    /**
     * @return BranchOccurrence
     */
    public function getBranchOccurrence()
    {
        return $this->branchOccurrence;
    }

    /**
     * @return Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * @return array
     */
    public function getSalesOrders()
    {
        return $this->salesOrders;
    }

    /**
     * @param array $orders
     */
    public function setSalesOrders(array $orders)
    {
        $this->salesOrders = array();

        foreach ($orders as $order) {
            $this->addSalesOrder($order);
        }
    }

    /**
     * @param ProducerSalesOrder $order
     *
     * @throws \LogicException
     */
    public function addSalesOrder(ProducerSalesOrder $order)
    {
        if ($order->getProducer()->getId() !== $this->producer->getId()) {
            throw new \LogicException('Invalid ProducerSalesOrder for ProducerBranchOccurrenceSalesOrders.');
        }

        $this->salesOrders[$order->getSalesOrder()->getId()] = $order;
    }

    /**
     * Get producer sales order sum
     *
     * @return float
     */
    public function getSum()
    {
        $sum = 0;

        foreach ($this->getSalesOrders() as $salesOrder) {
            $sum += $salesOrder->getTotal();
        }

        return $sum;
    }
}
