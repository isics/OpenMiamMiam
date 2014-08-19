<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\BranchOccurrence;

use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\ProducerRepository;

class BranchOccurrenceProducersAttendances
{
    /**
     * @var ProducerRepository
     */
    protected $producerRepository;

    /**
     * @var \Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence
     */
    protected $branchOccurrence;

    /**
     * @var array
     */
    protected $producersAttendanceYes = array();

    /**
     * @var array
     */
    protected $producersAttendanceNo = array();

    /**
     * @var array
     */
    protected $producersAttendanceUnknown = array();

    /**
     * Constructor
     *
     * @param ProducerRepository $producerRepository
     */
    public function __construct(ProducerRepository $producerRepository)
    {
        $this->producerRepository = $producerRepository;
    }

    /**
     * Set branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     */
    public function setBranchOccurrence(BranchOccurrence $branchOccurrence)
    {
        $this->branchOccurrence = $branchOccurrence;

        $this->compute();
    }

    /**
     * Get branch occurrence
     *
     * @return BranchOccurrence
     */
    public function getBranchOccurrence()
    {
        return $this->branchOccurrence;
    }

    /**
     * Compute attendances for all producers of branch occurrence
     */
    protected function compute()
    {
        $this->producersAttendanceYes = $this->producerRepository
            ->filterAttendances($this->branchOccurrence, true)
            ->getQuery()
            ->getResult();

        $this->producersAttendanceNo = $this->producerRepository
            ->filterAttendances($this->branchOccurrence, false)
            ->getQuery()
            ->getResult();

        $this->producersAttendanceUnknown = array_diff(
            $this->producerRepository->filterBranch($this->branchOccurrence->getBranch())->getQuery()->getResult(),
            $this->producersAttendanceYes,
            $this->producersAttendanceNo
        );
    }

    /**
     * @return array
     */
    public function getProducersAttendanceYes()
    {
        return $this->producersAttendanceYes;
    }

    /**
     * @return array
     */
    public function getProducersAttendanceNo()
    {
        return $this->producersAttendanceNo;
    }

    /**
     * @return array
     */
    public function getProducersAttendanceUnknown()
    {
        return $this->producersAttendanceUnknown;
    }
}