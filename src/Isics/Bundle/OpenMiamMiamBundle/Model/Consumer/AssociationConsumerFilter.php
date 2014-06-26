<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Consumer;

class AssociationConsumerFilter
{
    /**
     * @var int $ref
     */
    protected $ref;

    /**
     * @var string $lastName
     */
    protected $lastName;

    /**
     * @var string $firstName
     */
    protected $firstName;

    /**
     * @var bool $creditor
     */
    protected $creditor;

    /**
     * @return int
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param int $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return bool
     */
    public function isCreditor()
    {
        return $this->creditor;
    }

    /**
     * @param bool $creditor
     */
    public function setCreditor($creditor)
    {
        $this->creditor = $creditor;
    }
}