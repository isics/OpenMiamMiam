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

use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User as User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_processed", type="boolean")
     */
    private $isProcessed;

    /**
     * @var User $user
     * 
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamUserBundle\Entity\User", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

    /**
     * @var User $writer
     * 
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamUserBundle\Entity\User", inversedBy="writtenComments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="writer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $writer;

    /**
     * @var Association
     *
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\Association")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="association_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $association;

    /**
     * @var SalesOrder
     *
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sales_order_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $salesOrder;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->isProcessed = false;
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
     * Set sales order
     *
     * @param SalesOrder $salesOrder
     */
    public function setSalesOrder(SalesOrder $salesOrder = null)
    {
        $this->salesOrder = $salesOrder;

        return $this;
    }

    /**
     * Get sales order
     *
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Comment
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set isProcessed
     *
     * @param boolean $isProcessed
     * @return Comment
     */
    public function setIsProcessed($isProcessed)
    {
        $this->isProcessed = $isProcessed;
    
        return $this;
    }

    /**
     * Get isProcessed
     *
     * @return boolean 
     */
    public function getIsProcessed()
    {
        return $this->isProcessed;
    }

    /**
     * Set user
     *
     * @param \Isics\Bundle\openMiamMiamUserBundle\Entity\User $user
     * @return Comment
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Isics\Bundle\openMiamMiamUserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set writer
     *
     * @param \Isics\Bundle\openMiamMiamUserBundle\Entity\User $writer
     * @return Comment
     */
    public function setWriter(User $writer)
    {
        $this->writer = $writer;
    
        return $this;
    }

    /**
     * Get writer
     *
     * @return \Isics\Bundle\openMiamMiamUserBundle\Entity\User 
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $association
     */
    public function setAssociation(Association $association)
    {
        $this->association = $association;
    }

    /**
     * @return Association
     */
    public function getAssociation()
    {
        return $this->association;
    }
}
