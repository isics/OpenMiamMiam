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

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\ProducerAttendance;
use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerAttendance\ProducerAttendances;
use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerAttendance\ProducerBranchAttendances;
use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerAttendance\ProducerBranchOccurrenceAttendance;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ProducerAttendancesManager
 * Manager for producer attendances
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class ProducerAttendancesManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs object
     *
     * @param array $config
     * @param EntityManager $entityManager
     */
    public function __construct(array $config, EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->config = $resolver->resolve($config);
    }

    /**
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('nb_next_producer_attendances_to_define', 'upload_path'));
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
            $this->entityManager->flush();
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
            $this->entityManager->flush();
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
                $this->entityManager->remove($producerAttendance);
            }
        } else {
            if (null === $producerAttendance) {
                $producerAttendance = new ProducerAttendance();
                $producerAttendance->setProducer($branchOccurrenceAttendance->getProducer());
                $producerAttendance->setBranchOccurrence($branchOccurrenceAttendance->getBranchOccurrence());
            }
            $producerAttendance->setIsAttendee($branchOccurrenceAttendance->getAttendance() == ProducerBranchOccurrenceAttendance::ATTENDANCE_YES);

            $this->entityManager->persist($producerAttendance);
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Returns next attendances of a producer
     *
     * @param Producer $producer
     *
     * @return ProducerAttendances
     */
    public function getNextAttendancesOf(Producer $producer)
    {
        $branchOccurrenceRepository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence');
        $producerAttendanceRepository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:ProducerAttendance');

        $attendances = new ProducerAttendances($producer);
        foreach ($producer->getBranches() as $branch) {
            $branchAttendances = new ProducerBranchAttendances($producer, $branch);
            $branchOccurrences = $branchOccurrenceRepository->findAllNextForBranch(
                $branch,
                true,
                $this->config['nb_next_producer_attendances_to_define']
            );

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

    /**
     * Returns unknown attendances count
     *
     * @param ProducerAttendances $attendances
     *
     * @return int
     */
    public function getNbUnknownAttendances(ProducerAttendances $attendances)
    {
        $count = 0;
        foreach ($attendances as $branchAttendances) {
            $count += $this->getNbUnknownBranchAttendances($branchAttendances);
        }

        return $count;
    }

    /**
     * Returns unknown branch attendances count
     *
     * @param ProducerBranchAttendances $branchAttendances
     *
     * @return int
     */
    public function getNbUnknownBranchAttendances(ProducerBranchAttendances $branchAttendances)
    {
        $count = 0;
        foreach ($branchAttendances as $branchOccurrenceAttendance) {
            $count += $branchOccurrenceAttendance->getAttendance() ==  ProducerBranchOccurrenceAttendance::ATTENDANCE_UNKNOWN ?  1 : 0;
        }

        return $count;
    }
}
