<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Association;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class AllocatePayment
{
    /**
     * @var Association
     */
    private $association;

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $payments = array();

    /**
     * @var array
     */
    private $salesOrders = array();

    /**
     * Constructor
     *
     * @param Association $association
     * @param User        $user
     */
    public function __construct(Association $association, User $user = null)
    {
        $this->association = $association;
        $this->user        = $user;
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\Association
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamUserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @return array
     */
    public function getSalesOrders()
    {
        return $this->salesOrders;
    }
}