<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Association;

class ProducersTransfer
{
    /**
     * @var array
     */
    protected $branchOccurrences;

    /**
     * @var array
     */
    protected $producers;

    /**
     * @var array
     */
    protected $producersData;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var array
     */
    protected $totalByProducers;

    /**
     * @var array
     */
    protected $totalByBranchOccurrences;

    /**
     * Constructor
     *
     * @param array $branchOccurrences
     * @param array $producersData
     */
    public function __construct(array $branchOccurrences, array $producers, array $producersData, \DateTime $date)
    {
        $this->branchOccurrences = $branchOccurrences;
        $this->producers = $producers;
        $this->producersData = array();
        $this->date = $date;

        foreach ($producersData as $producersDatum) {
            if (!array_key_exists($producersDatum['producer_id'], $this->producersData)) {
                $this->producersData[$producersDatum['producer_id']] = array();
            }

            if (!array_key_exists($producersDatum['branch_occurrence_id'], $this->producersData[$producersDatum['producer_id']])) {
                $this->producersData[$producersDatum['producer_id']][$producersDatum['branch_occurrence_id']] = 0.0;
            }

            $this->producersData[$producersDatum['producer_id']][$producersDatum['branch_occurrence_id']] += isset($producersDatum['amount']) ? (float)$producersDatum['amount'] : 0.0;
        }
    }

    /**
     * Get branchOccurrences
     *
     * @return array
     */
    public function getBranchOccurrences()
    {
        return $this->branchOccurrences;
    }

    /**
     * Returns producers
     *
     * @return array
     */
    public function getProducers()
    {
        return $this->producers;
    }

    /**
     * Get total amount by producers
     */
    public function getTotalByProducers()
    {
        if (null === $this->totalByProducers){
            $this->totalByProducers = array();

            foreach ($this->producersData as $producerId => $producersDatum) {
                $this->totalByProducers[$producerId] = array_reduce($producersDatum, function($p1, $p2){
                    return (float)$p1 + (float)$p2;
                }, 0.0);
            }
        }

        return $this->totalByProducers;
    }

    /**
     * Get total amount by branch occurrences
     */
    public function getTotalByBranchOccurrences()
    {
        if (null === $this->totalByBranchOccurrences){
            $this->totalByBranchOccurrences = array();

            foreach ($this->producersData as $producerId => $producersDatum) {
                foreach ($producersDatum as $branchOccurrenceId => $amount) {
                    if (!array_key_exists($branchOccurrenceId, $this->totalByBranchOccurrences)) {
                        $this->totalByBranchOccurrences[$branchOccurrenceId] = 0.0;
                    }

                    $this->totalByBranchOccurrences[$branchOccurrenceId] += (float)$amount;
                }
            }
        }

        return $this->totalByBranchOccurrences;
    }

    /**
     * Returns total for producer id
     *
     * @param int $producerId
     *
     * @return float|null
     */
    public function getTotalForProducerId($producerId)
    {
        $totalByProducers = $this->getTotalByProducers();

        if (!array_key_exists($producerId, $totalByProducers)){
            return null;
        }

        return (float)$totalByProducers[$producerId];
    }

    /**
     * Returns total for branch occurrence id
     *
     * @param int $branchOccurrenceId
     *
     * @return float|null
     */
    public function getTotalForBranchOccurrenceId($branchOccurrenceId)
    {
        $totalByBranchOccurrences = $this->getTotalByBranchOccurrences();

        if (!array_key_exists($branchOccurrenceId, $totalByBranchOccurrences)){
            return null;
        }

        return (float)$totalByBranchOccurrences[$branchOccurrenceId];
    }

    /**
     * Returns sum for producer and branchOccurrence
     *
     * @param $producerId
     * @param $branchOccurrenceId
     *
     * @return float|null
     */
    public function getAmount($producerId, $branchOccurrenceId)
    {
        if (isset($this->producersData[$producerId][$branchOccurrenceId])) {
            return $this->producersData[$producerId][$branchOccurrenceId];
        }

        return null;
    }

    /**
     * Returns Export DateTime
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}