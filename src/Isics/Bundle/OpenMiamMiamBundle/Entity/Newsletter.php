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

use Doctrine\ORM\Mapping as ORM;

/**
 * Isics\OpenMiamMiamBundle\Entity\Newsletter
 *
 * @ORM\Table(name="newsletter")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\NewsletterRepository")
 */
class Newsletter
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
     * @var Association
     *
     * @ORM\ManyToOne(targetEntity="Association")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="association_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    private $association;
    
    /**
     * @ORM\ManyToMany(targetEntity="Association", inversedBy="newsletter")
     * @ORM\JoinTable(name="newsletter_has_association",
     *   joinColumns={
     *      @ORM\JoinColumn(name="newsletter_id", referencedColumnName="id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *      @ORM\JoinColumn(name="association_id", referencedColumnName="id", onDelete="CASCADE")
     *   }
     * )
     */
    private $associations;
    /**
     * @var string $recipientType
     *
     * @ORM\Column(name="recipient_type", type="string", length=64, nullable=true)
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
     * @var \DateTime $sendAt
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=false)
     */
    private $sentAt;

    /**
    * @ORM\ManyToMany(targetEntity="Branch", inversedBy="newsletter")
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
        $this->associations = new ArrayCollection();
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
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
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
     * Set to
     *
     * @param string $recipientType
     * @return Newsletter
     */
    public function setRecipientType($recipientType)
    {
        $this->recipientType = $recipientType;
    
        return $this;
    }
    
    /**
     * Get to
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }
    
    /**
     * Set from
     *
     * @param email $from
     * @return Newsletter
     */
    public function setFrom($from)
    {
        $this->from = $from;
    
        return $this;
    }
    
    /**
     * Get from
     *
     * @return email
     */
    public function getFrom()
    {
        return $this->from;
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
    
    /**
     * Add association
     *
     * @param Association $association
     * @return Newsletter
     */
    public function addAssociation(Association $association)
    {
        $this->associations[] = $association;
    
        return $this;
    }
    
    /**
     * Remove association
     *
     * @param Association $association
     */
    public function removeAssociation(Association $association)
    {
        $this->associations->removeElement($association);
    }
    
    /**
     * Get associations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociations()
    {
        return $this->associations;
    }
    
    /**
     * Set associations
     *
     * @param \Doctrine\Common\Collections\Collection $associations Associations
     */
    public function setAssociations($associations)
    {
        $this->associations = new ArrayCollection();
    
        foreach ($associations as $association) {
            $this->addAssociation($association);
        }
    }
    
    /**
     * Returns true if newsletter has association
     *
     * @param Association $association
     *
     * @return boolean
     */
    public function hasAssociation(Association $association)
    {
        foreach ($this->getAssociations() as $_association) {
            if ($_association->getId() === $association->getId()) {
                return true;
            }
        }
    
        return false;
    }
}