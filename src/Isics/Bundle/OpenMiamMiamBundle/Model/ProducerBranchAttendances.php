<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerBranchOccurrenceAttendance;

class ProducerBranchAttendances implements \IteratorAggregate
{
    /**
     * @var Producer $producer
     */
    protected $producer;

    /**
     * @var Branch $branch
     */
    protected $branch;

    /**
     * @var array $branchOccurrenceAttendances
     */
    protected $branchOccurrenceAttendances;

    /**
     * Constructs object
     *
     * @param Producer $producer
     * @param Branch $branch
     */
    public function __construct(Producer $producer, Branch $branch)
    {
        $this->producer = $producer;
        $this->branch = $branch;
        $this->branchOccurrenceAttendances = array();
    }

    /**
     * Returns producer
     *
     * @return Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Returns branch
     *
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Returns attendances for branch occurrence
     *
     * @return array
     */
    public function getBranchOccurrenceAttendances()
    {
        return $this->branchOccurrenceAttendances;
    }

    /**
     * Adds a branch occurrence attendance of a producer
     *
     * @param ProducerBranchOccurrenceAttendance $branchOccurrenceAttendance
     *
     * @throws \LogicException
     */
    public function addBranchOccurrenceAttendance(ProducerBranchOccurrenceAttendance $branchOccurrenceAttendance)
    {
        if ($branchOccurrenceAttendance->getProducer()->getId() !== $this->producer->getId()
            || $branchOccurrenceAttendance->getBranchOccurrence()->getBranch()->getId() !== $this->branch->getId()) {
            throw new \LogicException('Invalid ProducerBranchOccurrenceAttendance for ProducerBranchAttendances.');
        }

        $this->branchOccurrenceAttendances[$branchOccurrenceAttendance->getBranchOccurrence()->getId()] = $branchOccurrenceAttendance;
    }

    /**
     * {@inheritDoc}
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->branchOccurrenceAttendances);
    }
}
