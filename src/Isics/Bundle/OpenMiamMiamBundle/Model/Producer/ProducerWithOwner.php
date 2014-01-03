<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Producer;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class ProducerWithOwner
{
    /**
     * @var Producer $producer
     */
    protected $producer;

    /**
     * @var User $owner
     */
    protected $owner;

    /**
     * Sets Producer
     *
     * @param Producer
     */
    public function setProducer(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Returns Producer
     *
     * @return Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Sets owner
     *
     * @param User $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * Returns owner
     *
     * @return User|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @see Producer
     */
    public function setName($name)
    {
        $this->producer->setName($name);
    }

    /**
     * @see Producer
     */
    public function getName()
    {
        return $this->producer->getName();
    }

    /**
     * @see Producer
     */
    public function getAssociations()
    {
        return $this->producer->getAssociations();
    }

    /**
     * @see Producer
     */
    public function addAssociation(Association $association)
    {
        return $this->producer->addAssociation($association);
    }

    /**
     * @see Producer
     */
    public function removeAssociation(Association $association)
    {
        return $this->producer->removeAssociation($association);
    }
}