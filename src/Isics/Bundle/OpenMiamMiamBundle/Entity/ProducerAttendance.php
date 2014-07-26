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
 * Isics\OpenMiamMiamBundle\Entity\ProducerAttendance
 *
 * @ORM\Table(name="producer_attendance")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\ProducerAttendanceRepository")
 */
class ProducerAttendance
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
     * @var Producer
     *
     * @ORM\ManyToOne(targetEntity="Producer", inversedBy="producerAttendances")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="producer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $producer;

    /**
     * @var BranchOccurrence
     *
     * @ORM\ManyToOne(targetEntity="BranchOccurrence", inversedBy="producerAttendances")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="branch_occurrence_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $branchOccurrence;

    /**
     * @var boolean $isAttendee
     *
     * @ORM\Column(name="is_attendee", type="boolean", nullable=false)
     */
    private $isAttendee;



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
     * Set isAttendee
     *
     * @param boolean $isAttendee
     * @return ProducerAttendance
     */
    public function setIsAttendee($isAttendee)
    {
        $this->isAttendee = $isAttendee;

        return $this;
    }

    /**
     * Get isAttendee
     *
     * @return boolean
     */
    public function getIsAttendee()
    {
        return $this->isAttendee;
    }

    /**
     * Set producer
     *
     * @param Producer $producer
     * @return ProducerAttendance
     */
    public function setProducer(Producer $producer)
    {
        $this->producer = $producer;

        return $this;
    }

    /**
     * Get producer
     *
     * @return Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Set branchOccurrence
     *
     * @param BranchOccurrence $branchOccurrence
     * @return ProducerAttendance
     */
    public function setBranchOccurrence(BranchOccurrence $branchOccurrence)
    {
        $this->branchOccurrence = $branchOccurrence;

        return $this;
    }

    /**
     * Get branchOccurrence
     *
     * @return BranchOccurrence
     */
    public function getBranchOccurrence()
    {
        return $this->branchOccurrence;
    }
}
