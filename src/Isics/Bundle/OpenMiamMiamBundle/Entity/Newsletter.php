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
 * Isics\OpenMiamMiamBundle\Entity\Newsletter
 *
 * @ORM\Table(name="newsletter")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\NewsletterRepository")
 */
class Newsletter
{
    const RECIPIENT_TYPE_ALL      = 0;
    const RECIPIENT_TYPE_PRODUCER = 1;
    const RECIPIENT_TYPE_CONSUMER = 2;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Association
     *
     * @ORM\ManyToOne(targetEntity="Association")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="association_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    private $association;

    /**
     * @var integer $recipientType
     *
     * @ORM\Column(name="recipient_type", type="integer", nullable=false)
     */
    private $recipientType;

    /**
     * @var string $subject
     *
     * @ORM\Column(name="subject", type="string", length=128, nullable=false)
     */
    private $subject;

    /**
     * @var string $body
     *
     * @ORM\Column(name="body", type="text", nullable=false)
     */
    private $body;

    /**
     * @var \DateTime $sentAt
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @var integer $nbRecipients
     *
     * @ORM\Column(name="nb_recipients", type="integer", nullable=true)
     */
    private $nbRecipients;

    /**
     * @var Doctrine\Common\Collections\Collection $branches
     *
     * @ORM\ManyToMany(targetEntity="Branch", inversedBy="newsletters")
     * @ORM\JoinTable(name="newsletter_has_branch",
     *   joinColumns={
     *      @ORM\JoinColumn(name="newsletter_id", referencedColumnName="id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *      @ORM\JoinColumn(name="branch_id", referencedColumnName="id", onDelete="CASCADE")
     *   }
     * )
     */
    private $branches;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->branches = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Newsletter
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set association
     *
     * @param Association $association
     * @return Newsletter
     */
    public function setAssociation(Association $association = null)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return Association
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * Set recipientType
     *
     * @param integer $recipientType
     * @return Newsletter
     */
    public function setRecipientType($recipientType)
    {
        $this->recipientType = $recipientType;

        return $this;
    }

    /**
     * Get recipientType
     *
     * @return integer
     */
    public function getRecipientType()
    {
        return $this->recipientType;
    }

    /**
     * Set subject
     *
     * @param string $from
     * @return Newsletter
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Newsletter
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set sentAt
     *
     * @param dateTime $sentAt
     * @return Newsletter
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set nbRecipients
     *
     * @param integer $nbRecipients
     * @return Newsletter
     */
    public function setNbRecipients($nbRecipients)
    {
        $this->nbRecipients = $nbRecipients;

        return $this;
    }

    /**
     * Get nbRecipient
     *
     * @return integer
     */
    public function getNbRecipients()
    {
        return $this->nbRecipients;
    }

    /**
     * Add branch
     *
     * @param Branch $branch
     * @return Newsletter
     */
    public function addBranch(Branch $branch)
    {
        $this->branches[] = $branch;

        return $this;
    }

    /**
     * Remove branch
     *
     * @param Branch $branch
     */
    public function removeBranch(Branch $branch)
    {
        $this->branches->removeElement($branch);
    }

    /**
     * Get branches
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBranches()
    {
        return $this->branches;
    }

    /**
     * Set branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches Branches
     */
    public function setBranches($branches)
    {
        $this->branches = new ArrayCollection();

        foreach ($branches as $branch) {
            $this->addBranch($branch);
        }
    }

    /**
     * Returns true if newsletter has branch
     *
     * @param Branch $branch
     *
     * @return boolean
     */
    public function hasBranch(Branch $branch)
    {
        foreach ($this->getBranches() as $_branch) {
            if ($_branch->getId() === $branch->getId()) {
                return true;
            }
        }

        return false;
    }
}