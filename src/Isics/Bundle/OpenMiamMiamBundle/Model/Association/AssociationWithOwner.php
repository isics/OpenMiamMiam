<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Association;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class AssociationWithOwner
{
    /**
     * @var Association $association
     */
    protected $association;

    /**
     * @var User $owner
     */
    protected $owner;

    /**
     * @var string
     */
    protected $email;



    /**
     * Sets Association
     *
     * @param Association
     */
    public function setAssociation(Association $association)
    {
        $this->association = $association;
    }

    /**
     * Returns Association
     *
     * @return Association
     */
    public function getAssociation()
    {
        return $this->association;
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
     * @see Association
     */
    public function setName($name)
    {
        $this->association->setName($name);
    }

    /**
     * @see Association
     */
    public function getName()
    {
        return $this->association->getName();
    }

    /**
     * @see Association
     */
    public function setEmail($email)
    {
        $this->association->setEmail($email);
    }

    /**
     * @see Association
     */
    public function getEmail()
    {
        return $this->association->getEmail();
    }
}
