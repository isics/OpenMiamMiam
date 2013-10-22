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
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @var string $recipientType
     *
     * @ORM\Column(name="recipient_type", type="string", length=64, nullable=true)
     */
    private $recipientType;
    
    /**
     * @var string $from
     *
     * @ORM\Column(name="from", type="email", nullable=false)
     */
    private $from;
    
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
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="send_at", type="datetime", nullable=false)
     */
    private $sendAt;

    /**
     * @var string $isConfirmed
     *
     * @ORM\Column(name="$is_Confirmed", type="boolean", nullable=false)
     */
    private $isConfirmed;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->isConfirmed = false;
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
    public function getSendAt()
    {
        return $this->sendAt;
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
     * Set isConfirmed
     *
     * @param boolean $isConfirmed
     * @return Newsletter
     */
    public function setIsConfirmed($isConfirmed)
    {
        $this->isConfirmed = $isConfirmed;
    
        return $this;
    }
    
    /**
     * Get isConfirmed
     *
     * @return boolean
     */
    public function getIsConfirmed()
    {
        return $this->isConfirmed;
    }
}
