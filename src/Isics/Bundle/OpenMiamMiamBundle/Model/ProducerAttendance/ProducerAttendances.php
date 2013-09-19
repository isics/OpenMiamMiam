<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\ProducerAttendance;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerAttendance\ProducerBranchAttendances;

class ProducerAttendances implements \IteratorAggregate
{
    /**
     * @var Producer $producer
     */
    protected $producer;

    /**
     * @var array $attendances
     */
    protected $branchAttendances;

    /**
     * Constructs object
     *
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
        $this->branchAttendances = array();
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
     * Returns branch attendances of producer
     *
     * @return array
     */
    public function getBranchAttendances()
    {
        return $this->branchAttendances;
    }

    /**
     * Adds a branch attendances of a producer
     *
     * @param ProducerBranchAttendances $branchAttendances
     *
     * @throws \LogicException
     */
    public function addBranchAttendances(ProducerBranchAttendances $branchAttendances)
    {
        if ($branchAttendances->getProducer()->getId() !== $this->producer->getId()) {
            throw new \LogicException('Invalid ProducerBranchAttendances for ProducerAttendances.');
        }

        $this->branchAttendances[$branchAttendances->getBranch()->getId()] = $branchAttendances;
    }

    /**
     * {@inheritDoc}
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->branchAttendances);
    }
}
