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

class ProducerWithOwner
{
    /**
     * @var Producer $producer
     */
    protected $producer;

    /**
     * @var string $ownerEmail
     */
    protected $ownerEmail;

    /**
     * Constructor
     *
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
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
     * @see Producer
     */
    public function getName()
    {
        return $this->producer->getName();
    }

    /**
     * @see Producer
     */
    public function setName($name)
    {
        $this->producer->setName($name);
    }

    /**
     * Returns owner email
     *
     * @return string
     */
    public function getOwnerEmail()
    {
        return $this->ownerEmail;
    }

    /**
     * Sets owner email
     *
     * @param string $ownerEmail
     */
    public function setOwnerEmail($ownerEmail)
    {
        $this->ownerEmail = $ownerEmail;
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