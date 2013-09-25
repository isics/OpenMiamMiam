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
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Isics\OpenMiamMiamBundle\Entity\ActivityLog
 *
 * @ORM\Table(name="activity_log")
 * @ORM\Entity
 */
class ActivityLog
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
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamUserBundle\Entity\User", inversedBy="activityLogs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @var string $userName
     *
     * @ORM\Column(name="user_name", type="string", length=255, nullable=false)
     */
    private $userName;

    /**
     * @var string $message
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=false)
     */
    private $message;

    /**
     * @var string $objectId
     *
     * @ORM\Column(name="object_id", type="integer", nullable=true)
     */
    private $objectId;

    /**
     * @var string $objectType
     *
     * @ORM\Column(name="object_type", type="string", length=32, nullable=true)
     */
    private $objectType;

    /**
     * @var string $targetId
     *
     * @ORM\Column(name="target_id", type="integer", nullable=true)
     */
    private $targetId;

    /**
     * @var string $targetType
     *
     * @ORM\Column(name="target_type", type="string", length=32, nullable=true)
     */
    private $targetType;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param string $targetId
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }

    /**
     * @return string
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @param string $targetType
     */
    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;
    }

    /**
     * @return string
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }
}
