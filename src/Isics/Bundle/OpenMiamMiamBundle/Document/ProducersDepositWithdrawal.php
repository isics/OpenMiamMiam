<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Document;

use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;

class ProducersDepositWithdrawal
{
    /**
     * @var BranchOccurrence
     */
    protected $branchOccurrence;

    /**
     * @var array
     */
    protected $salesOrderRowsData;

    /**
     * @var string
     */
    protected $artificial_product_ref;


    /**
     * Constructor
     *
     * @param BranchOccurrence $branchOccurrence
     * @param array $salesOrderRowsData
     * @param $artificial_product_ref
     */
    public function __construct(BranchOccurrence $branchOccurrence, array $salesOrderRowsData, $artificial_product_ref)
    {
        $this->branchOccurrence = $branchOccurrence;
        $this->salesOrderRowsData = $salesOrderRowsData;
        $this->artificial_product_ref = $artificial_product_ref;
    }

    /**
     * Get branchOccurrences
     *
     * @return array
     */
    public function getBranchOccurrence()
    {
        return $this->branchOccurrence;
    }

    /**
     * Returns producers
     *
     * @return array
     */
    public function getProducers()
    {
        $producers = array();
        foreach($this->salesOrderRowsData as $salesOrderRowData) {
            $producers[$salesOrderRowData['producer_id']] = $salesOrderRowData['producer_name'];
        }
        asort($producers);
        return $producers;
    }

    /**
     * Get grouped sales order rows data for branch
     *
     * @param int $producerId
     * @return array
     */
    public function getGroupedSalesOrderRowsMiscellaneousData($producerId)
    {
        $groups = array();

        foreach($this->getSalesOrderRowsDataForProducerId($producerId) as $salesOrderRowData){
            $groupKey = $this->getGroupKeySalesOrderRow($salesOrderRowData);

            if($salesOrderRowData['product_ref'] == $this->artificial_product_ref) {
                if (!array_key_exists($groupKey, $groups)) {
                    $groups[$groupKey] = $salesOrderRowData;
                }
                else {
                    $group =& $groups[$groupKey];
                    $group['product_quantity'] += $salesOrderRowData['product_quantity'];
                    $group['product_total'] += $salesOrderRowData['product_total'];
                }
            }
        }
        return $groups;
    }

    /**
     * Get sales order rows data for producer id
     *
     * @param $producerId
     * @return array
     */
    public function getSalesOrderRowsDataForProducerId($producerId)
    {
        return array_filter($this->salesOrderRowsData, function($salesOrderRowData) use ($producerId) {
            return $salesOrderRowData['producer_id'] == $producerId;
        });
    }

    /**
     * Get grouped sales order rows data for producer id
     *
     * @param $producerId
     * @return array
     */
    public function getGroupedSalesOrderRowsDataForProducerId($producerId)
    {
        $groups = array();

        foreach($this->getSalesOrderRowsDataForProducerId($producerId) as $salesOrderRowData){

            $groupKey = $this->getGroupKeySalesOrderRow($salesOrderRowData);

            if($salesOrderRowData['product_ref'] != $this->artificial_product_ref) {
                if (!array_key_exists($groupKey, $groups)) {
                    $groups[$groupKey] = $salesOrderRowData;
                }
                else {
                    $group =& $groups[$groupKey];
                    $group['product_quantity'] += $salesOrderRowData['product_quantity'];
                    $group['product_total'] += $salesOrderRowData['product_total'];
                }
            }
        }

        ksort($groups);

        return $groups;
    }

    /**
     * Get group key sales order row
     *
     * @param $salesOrderRowData
     * @return string
     */
    private function getGroupKeySalesOrderRow($salesOrderRowData)
    {
        return strtr('%ref%_at_%price%', array(
            '%ref%' => $salesOrderRowData['product_ref'],
            '%price%' => $salesOrderRowData['product_unit_price']
        ));
    }

    /**
     * get grouped commission data for producer id
     *
     * @param $producerId
     * @return array
     */
    public function getGroupedCommissionDataForProducerId($producerId)
    {
        $groups = array();

        foreach($this->getSalesOrderRowsDataForProducerId($producerId) as $salesOrderRowData){

            if (!array_key_exists($salesOrderRowData['product_commission'], $groups)) {
                $groups[$salesOrderRowData['product_commission']] = $salesOrderRowData['product_total'] * $salesOrderRowData['product_commission']/100;
            }
            else {
                $group =& $groups[$salesOrderRowData['product_commission']];
                $group += $salesOrderRowData['product_total'] * $salesOrderRowData['product_commission']/100;
            }
        }

        ksort($groups);

        return $groups;
    }

    /**
     * Get total for producer id
     *
     * @param $producerId
     * @return int
     */
    public function getTotalForProducerId($producerId)
    {
        $groups = $this->getGroupedSalesOrderRowsDataForProducerId($producerId);
        $totalProducer = 0;

        foreach($groups as $group) {
            $totalProducer = $totalProducer + $group['product_total'];
        }
        return $totalProducer;
    }

    /**
     * Get branch total for producer id
     *
     * @param $producerId
     * @return float
     */
    public function getBranchOccurrenceTotalForProducerId($producerId)
    {
        $groups = $this->getGroupedSalesOrderRowsMiscellaneousData($producerId);
        $totalBranch = 0;

        foreach($groups as $group) {
            $totalBranch = $totalBranch + $group['product_total'];
        }

        return $totalBranch;
    }

    /**
     * Get total
     *
     * @param $producerId
     * @return float
     */
    public function getTotal($producerId)
    {
        return $this->getTotalForProducerId($producerId) + $this->getBranchOccurrenceTotalForProducerId($producerId);
    }

    /**
     * Get total to pay
     *
     * @param $producerId
     * @return float
     */
    public function getTotalToPay($producerId)
    {
        $totalCommission = 0;

        foreach ($this->getGroupedCommissionDataForProducerId($producerId) as  $commission => $productsTotal) {
            $totalCommission = $totalCommission + $productsTotal;
        }

        return $this->getTotal($producerId) - $totalCommission;
    }

}