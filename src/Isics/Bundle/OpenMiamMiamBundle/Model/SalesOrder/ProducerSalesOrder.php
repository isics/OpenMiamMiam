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
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;

class ProducerSalesOrder
{
    /**
     * @var Producer
     */
    protected $producer;

    /**
     * @var SalesOrder
     */
    protected $salesOrder;

    /**
     * @var array
     */
    protected $salesOrderRows;

    /**
     * Constructs ProducerSalesOrder
     *
     * @param Producer $producer
     * @param SalesOrder $salesOrder
     */
    function __construct(Producer $producer, SalesOrder $salesOrder)
    {
        $this->producer = $producer;
        $this->salesOrder = $salesOrder;

        foreach ($this->salesOrder->getSalesOrderRows() as $row) {
            if ($row->getProducer()->getId() == $this->producer->getId()) {
                $this->salesOrderRows[] = $row;
            }
        }
    }

    /**
     * @return Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    /**
     * @return array
     */
    public function getSalesOrderRows()
    {
        return $this->salesOrderRows;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function setSalesOrderRows(array $rows)
    {
        $this->salesOrderRows = array();
        foreach ($rows as $row) {
            $this->addSalesOrderRow($row);
        }
    }

    /**
     * @param SalesOrderRow $row
     */
    public function addSalesOrderRows(SalesOrderRow $row)
    {
        $this->salesOrderRows[] = $row;
    }

    /**
     * Returns total
     *
     * @return float
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->salesOrderRows as $row) {
            $total += $row->getTotal();
        }

        return $total;
    }
}
