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
 * Comment
 *
 * @ORM\Table()
 * @ORM\Entity
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
     * @ORM\Column(name="writingDate", type="datetime")
     */
    private $writingDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isProcessed", type="boolean")
     */
    private $isProcessed;

    /**
     * @var User $user
     * 
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamUserBundle\Entity\User", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @var User $writer
     * 
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamUserBundle\Entity\User", inversedBy="writtenComments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $writer;


    /**
     * Constructor
     */
    public function __construc()
    {
        $this->writingdate = new \Datetime();
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
     * Set writingDate
     *
     * @param \DateTime $writingDate
     * @return Comment
     */
    public function setWritingDate($writingDate)
    {
        $this->writingDate = $writingDate;
    
        return $this;
    }

    /**
     * Get writingDate
     *
     * @return \DateTime 
     */
    public function getWritingDate()
    {
        return $this->writingDate;
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
    public function setUser(\Isics\Bundle\openMiamMiamUserBundle\Entity\User $user)
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
    public function setWriter(\Isics\Bundle\openMiamMiamUserBundle\Entity\User $writer)
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
}
