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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class ProducerSalesOrders
{
    /**
     * @var Producer $producer
     */
    protected $producer;

    /**
     * @var array
     */
    protected $branchOccurrencesSalesOrders;

    /**
     * Constructs object
     *
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
        $this->branchOccurrencesSalesOrders = array();
    }

    /**
     * @param ProducerBranchOccurrenceSalesOrders $branchOccurrenceSalesOrders
     *
     * @throws \LogicException
     */
    public function addProducerBranchOccurrenceSalesOrders(ProducerBranchOccurrenceSalesOrders $branchOccurrenceSalesOrders)
    {
        if ($branchOccurrenceSalesOrders->getProducer()->getId() !== $this->producer->getId()) {
            throw new \LogicException('Invalid $branchOccurrenceSalesOrders for ProducerSalesOrders.');
        }

        $this->branchOccurrencesSalesOrders[$branchOccurrenceSalesOrders->getBranchOccurrence()->getId()] = $branchOccurrenceSalesOrders;
    }

    /**
     * Returns branchOccurrencesSalesOrders
     */
    public function getBranchOccurrencesSalesOrders()
    {
        return $this->branchOccurrencesSalesOrders;
    }

    /**
     * Returns count sales order
     */
    public function countSalesOrders()
    {
        $count = 0;
        foreach ($this->branchOccurrencesSalesOrders as $branchOccurrenceSalesOrder) {
            foreach ($branchOccurrenceSalesOrder->getSalesOrders() as $salesOrders)
            $count += count($salesOrders);
        }

        return $count;
    }
}
