<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Association;

use Doctrine\Common\Collections\ArrayCollection;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
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
     * @param array|ArrayCollection $payments
     */
    public function setPayments($payments)
    {
        if ($payments instanceof ArrayCollection) {
            $payments = $payments->toArray();
        }

        if (!is_array($payments)) {
            throw new \InvalidArgumentException('Parameter must be an array or an ArrayCollection instance');
        }

        $this->payments = $payments;
    }

    /**
     * @return array
     */
    public function getSalesOrders()
    {
        return $this->salesOrders;
    }

    /**
     * @param array|ArrayCollection $salesOrders
     */
    public function setSalesOrders($salesOrders)
    {
        if ($salesOrders instanceof ArrayCollection) {
            $salesOrders = $salesOrders->toArray();
        }

        if (!is_array($salesOrders)) {
            throw new \InvalidArgumentException('Parameter must be an array or an ArrayCollection instance');
        }

        $this->salesOrders = $salesOrders;
    }
}