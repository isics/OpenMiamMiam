<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\ProducerAttendance;
use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerAttendances;
use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerBranchAttendances;
use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerBranchOccurrenceAttendance;

/**
 * Class ProducerAttendancesManager
 * Manager for producer attendances
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class ProducerAttendancesManager
{
    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * Constructs object
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Updates attendances
     *
     * @param ProducerAttendances $attendances
     * @param boolean $flush
     */
    public function updateAttendances(ProducerAttendances $attendances, $flush = true)
    {
        foreach ($attendances as $branchAttendances) {
            $this->updateBranchAttendances($branchAttendances, false);
        }

        if ($flush) {
            $this->objectManager->flush();
        }
    }

    /**
     * Updates branch attendances
     *
     * @param ProducerBranchAttendances $branchAttendances
     * @param boolean $flush
     */
    public function updateBranchAttendances(ProducerBranchAttendances $branchAttendances, $flush = true)
    {
        foreach ($branchAttendances as $branchOccurrenceAttendance) {
            $this->updateBranchOccurrenceAttendance($branchOccurrenceAttendance, false);
        }

        if ($flush) {
            $this->objectManager->flush();
        }
    }

    /**
     * Updates branch occurrence attendance
     *
     * @param ProducerBranchOccurrenceAttendance $branchOccurrenceAttendance
     * @param boolean $flush
     */
    public function updateBranchOccurrenceAttendance(ProducerBranchOccurrenceAttendance $branchOccurrenceAttendance, $flush = true)
    {
        $producerAttendance = $branchOccurrenceAttendance->getProducerAttendance();

        if ($branchOccurrenceAttendance->getAttendance() == ProducerBranchOccurrenceAttendance::ATTENDANCE_UNKNOWN) {
            if (null !== $producerAttendance) {
                $this->objectManager->remove($producerAttendance);
            }
        } else {
            if (null === $producerAttendance) {
                $producerAttendance = new ProducerAttendance();
                $producerAttendance->setProducer($branchOccurrenceAttendance->getProducer());
                $producerAttendance->setBranchOccurrence($branchOccurrenceAttendance->getBranchOccurrence());
            }
            $producerAttendance->setIsAttendee($branchOccurrenceAttendance->getAttendance() == ProducerBranchOccurrenceAttendance::ATTENDANCE_YES);

            $this->objectManager->persist($producerAttendance);
        }

        if ($flush) {
            $this->objectManager->flush();
        }
    }

    /**
     * Returns next attendances of a producer
     *
     * @param Producer $producer
     * @param boolean $open
     * @param int $limit
     *
     * @return ProducerAttendances
     */
    public function getNextAttendancesOf(Producer $producer, $open = true, $limit = null)
    {
        $branchOccurrenceRepository = $this->objectManager->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence');
        $producerAttendanceRepository = $this->objectManager->getRepository('IsicsOpenMiamMiamBundle:ProducerAttendance');

        $attendances = new ProducerAttendances($producer);
        foreach ($producer->getBranches() as $branch) {
            $branchAttendances = new ProducerBranchAttendances($producer, $branch);
            $branchOccurrences = $branchOccurrenceRepository->findAllNextForBranch($branch, $open, $limit);

            $producerAttendances = $producerAttendanceRepository->findBy(array(
                'producer' => $producer,
                'branchOccurrence' => $branchOccurrences
            ));

            foreach ($branchOccurrences as $occurrence) {
                $branchOccurrenceAttendance = new ProducerBranchOccurrenceAttendance($producer, $occurrence);

                $producerAttendance = null;
                foreach ($producerAttendances as $attendance) {
                    if ($attendance->getBranchOccurrence()->getId() == $occurrence->getId()) {
                        $producerAttendance = $attendance;
                        break;
                    }
                }
                $branchOccurrenceAttendance->setProducerAttendance($producerAttendance);

                $branchAttendances->addBranchOccurrenceAttendance($branchOccurrenceAttendance);
            }

            $attendances->addBranchAttendances($branchAttendances);
        }

        return $attendances;
    }
}
