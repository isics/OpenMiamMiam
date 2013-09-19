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

use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\ProducerAttendance;

class ProducerBranchOccurrenceAttendance
{
    const ATTENDANCE_UNKNOWN = 0;
    const ATTENDANCE_YES = 1;
    const ATTENDANCE_NO = 2;

    /**
     * @var Producer $producer
     */
    protected $producer;

    /**
     * @var BranchOccurrence $branchOccurrence
     */
    protected $branchOccurrence;

    /**
     * Returns ProducerAttendance
     *
     * @var ProducerAttendance $producerAttendance
     */
    protected $producerAttendance;

    /**
     * @var int $attendance
     */
    protected $attendance;

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
     * Returns branchOccurrence
     *
     * @return BranchOccurrence
     */
    public function getBranchOccurrence()
    {
        return $this->branchOccurrence;
    }

    /**
     * Returns ProducerAttendance
     *
     * @return ProducerAttendance|false
     */
    public function getProducerAttendance()
    {
        return $this->producerAttendance;
    }

    /**
     * Sets the attendance of producer
     *
     * @param ProducerAttendance $producerAttendance
     *
     * @throws \LogicException
     */
    public function setProducerAttendance(ProducerAttendance $producerAttendance = null)
    {
        if (null !== $producerAttendance
            && ($producerAttendance->getBranchOccurrence()->getId() !== $this->getBranchOccurrence()->getId()
                || $producerAttendance->getProducer()->getId() !== $this->producer->getId())) {
            throw new \LogicException('Invalid ProducerAttendance for ProducerBranchOccurrenceAttendance.');
        }

        $this->producerAttendance = $producerAttendance;
    }

    /**
     * Returns the producer attendance
     *
     * @return int
     */
    public function getAttendance()
    {
        if (null === $this->attendance) {
            if (null === $this->producerAttendance) {
                $this->attendance = self::ATTENDANCE_UNKNOWN;
            } else {
                $this->attendance = $this->producerAttendance->getIsAttendee() ? self::ATTENDANCE_YES : self::ATTENDANCE_NO;
            }
        }

        return $this->attendance;
    }

    /**
     * Sets attendance
     *
     * @param $attendance
     *
     * @throws \InvalidArgumentException
     */
    public function setAttendance($attendance)
    {
        if (!in_array($attendance, self::getAttendancesStatus())) {
            throw new \InvalidArgumentException('Invalid attendance status : '.$attendance);
        }

        $this->attendance = $attendance;
    }

    /**
     * Returns attendances status
     *
     * @return array
     */
    public static function getAttendancesStatus()
    {
        return array(
            self::ATTENDANCE_UNKNOWN,
            self::ATTENDANCE_YES,
            self::ATTENDANCE_NO
        );
    }
}
