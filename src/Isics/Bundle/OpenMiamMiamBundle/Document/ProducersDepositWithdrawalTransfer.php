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

class ProducersDepositWithdrawalTransfer
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
     * @var array
     */
    protected $artificial_product_ref = 'MISC';

    /**
     * Constructor
     *
     * @param BranchOccurrence $branchOccurrence
     * @param array $salesOrderRowsData
     *
     */
    public function __construct(BranchOccurrence $branchOccurrence, array $salesOrderRowsData)
    {
        $this->branchOccurrence = $branchOccurrence;
        $this->salesOrderRowsData = $salesOrderRowsData;
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
     * Get Grouped Sales Order Rows Data For Branch
     *
     * @param $producerId
     * @return array
     */
    public function getGroupedSalesOrderRowsDataForBranch($producerId)
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
     * Get Sales Order Rows Data For Producer Id
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
     * Get Grouped Sales Order Rows Data For Producer Id
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
        return $groups;
    }

    /**
     * Get Group Key Sales Order Row
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
     * get Grouped Commission Data For Producer Id
     *
     * @param $producerId
     * @return array
     */
    public function getGroupedCommissionDataForProducerId($producerId)
    {
        $groups = array();

        foreach($this->getSalesOrderRowsDataForProducerId($producerId) as $salesOrderRowData){

            if (!array_key_exists($salesOrderRowData['product_commission'], $groups)) {
                $groups[$salesOrderRowData['product_commission']] = $salesOrderRowData['product_total'];
            }
            else {
                $group =& $groups[$salesOrderRowData['product_commission']];
                $group += $salesOrderRowData['product_total'];
            }
        }

        return $groups;
    }

    /**
     * Get Total For Producer Id
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
     * Get Branch Total For Producer Id
     *
     * @param $producerId
     * @return int
     */
    public function getBranchTotalForProducerId($producerId)
    {
        $groups = $this->getGroupedSalesOrderRowsDataForBranch($producerId);
        $totalBranch = 0;

        foreach($groups as $group) {
            $totalBranch = $totalBranch + $group['product_total'];
        }

        return $totalBranch;
    }

    /**
     * Get Total
     *
     * @param $producerId
     * @return int
     */
    public function getTotal($producerId)
    {
        $totalProducers =$this->getTotalForProducerId($producerId);
        $totalBranch = $this->getBranchTotalForProducerId($producerId);
        $total = $totalProducers + $totalBranch;

        return($total);
    }

    /**
     * Get Total Commission
     *
     * @param $commission
     * @param $productsTotal
     * @return float
     */
    public function getTotalCommission($commission, $productsTotal)
    {
        $totalCommission = $productsTotal * $commission/100;

        return $totalCommission;
    }

    /**
     * Get Total To Pay
     *
     * @param $producerId
     * @return int
     */
    public function getTotalToPay($producerId)
    {
        $totalCommission = 0;
        $total = $this->getTotal($producerId);

        foreach ($this->getGroupedCommissionDataForProducerId($producerId) as  $commission => $productsTotal) {
            $totalCommission = $totalCommission + $this->getTotalCommission($commission, $productsTotal);
        }

        return($total - $totalCommission);
    }

}