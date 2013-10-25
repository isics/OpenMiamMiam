<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Isics\OpenMiamMiamBundle\Entity\BranchOccurrence
 *
 * @ORM\Table(name="branch_occurrence")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchOccurrenceRepository")
 */
class BranchOccurrence
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Branch
     *
     * @ORM\ManyToOne(targetEntity="Branch")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $branch;

    /**
     * @var \DateTime $begin
     *
     * @ORM\Column(name="begin", type="datetime", nullable=false)
     */
    private $begin;

    /**
     * @var \DateTime $end
     *
     * @ORM\Column(name="end", type="datetime", nullable=false)
     */
    private $end;

    /**
     * @var Doctrine\Common\Collections\Collection $producerAttendances
     *
     * @ORM\OneToMany(targetEntity="ProducerAttendance", mappedBy="branchOccurrence")
     */
    private $producerAttendances;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->producerAttendances = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set begin
     *
     * @param \DateTime $begin
     *
     * @return BranchDate
     */
    public function setBegin(\DateTime $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * Get begin
     *
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @see getBegin()
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->getBegin();
    }

    /**
     * @see setBegin()
     */
    public function setDate(\DateTime $date)
    {
        return $this->setBegin($date);
    }

    /**
     * @see getBegin()
     *
     * @return \DateTime
     */
    public function getBeginTime()
    {
        return $this->getBegin();
    }

    /**
     * Set begin time
     *
     * @param \DateTime
     */
    public function setBeginTime($time)
    {
        $this->begin->setTime(
            (int) $time->format('H'),
            (int) $time->format('i')
        );
    }

    /**
     * Set end
     *
     * @param \DateTime $ebd
     *
     * @return BranchDate
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @see getEnd()
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->getEnd();
    }

    /**
     * Set end time
     *
     * @param \DateTime
     */
    public function setEndTime($time)
    {
        $this->setEnd(clone $this->getBegin());

        $this->end->setTime(
            (int) $time->format('H'),
            (int) $time->format('i')
        );
    }

    /**
     * Set branch
     *
     * @param  Branch $branch
     *
     * @return BranchDate
     */
    public function setBranch(Branch $branch)
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * Get branch
     *
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Add producer attendance
     *
     * @param ProducerAttendance $producerAttendance
     * @return BranchOccurrence
     */
    public function addProducerAttendance(ProducerAttendance $producerAttendance)
    {
        $this->producerAttendances[] = $producerAttendance;

        return $this;
    }

    /**
     * Remove producera ttendance
     *
     * @param ProducerAttendance $producerAttendance
     */
    public function removeProducerAttendance(ProducerAttendance $producerAttendance)
    {
        $this->producerAttendances->removeElement($producerAttendance);
    }

    /**
     * Get producer attendances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducerAttendances()
    {
        return $this->producerAttendances;
    }

    /**
     * Returns true if producer is attendee, false else or null if not defined
     *
     * @param Producer $producer
     *
     * @return boolean|null
     */
    public function isProducerAttendee(Producer $producer)
    {
        foreach ($this->getProducerAttendances() as $producerAttendance) {
            if ($producerAttendance->getProducer() === $producer) {
                return $producerAttendance->getIsAttendee();
            }
        }

        return null;
    }
}
