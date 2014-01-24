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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\PaymentAllocation;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

/**
 * Isics\OpenMiamMiamBundle\Entity\Payment
 *
 * @ORM\Table(name="payment")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\PaymentRepository")
 */
class Payment
{
    const TYPE_CASH        = 1;
    const TYPE_CHEQUE      = 2;
    const TYPE_CREDIT_CARD = 3;
    const TYPE_TRANSFER    = 4;



    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int $type
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var float $rest
     *
     * @ORM\Column(name="rest", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $rest;

    /**
     * @var \DateTime $date
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="PaymentAllocation", mappedBy="payment", cascade="all", orphanRemoval=true)
     */
    private $paymentAllocations;

    /**
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamUserBundle\Entity\User", inversedBy="payments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

    /**
     * @var Association $association
     *
     * @ORM\ManyToOne(targetEntity="Association", inversedBy="payments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="association_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $association;



    /**
     * Constructs object
     */
    public function __construct()
    {
        $this->rest = 0;
        $this->paymentAllocations = new ArrayCollection();
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
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
     * @param Association $association
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

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $rest
     */
    public function setRest($rest)
    {
        $this->rest = $rest;
    }

    /**
     * @return float
     */
    public function getRest()
    {
        return $this->rest;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

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
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns types
     *
     * @return array
     */
    public static function getTypes()
    {
        return array(self::TYPE_CASH, self::TYPE_CHEQUE, self::TYPE_CREDIT_CARD, self::TYPE_TRANSFER);
    }

    /**
     * @param $paymentAllocations
     */
    public function setPaymentAllocations($paymentAllocations)
    {
        $this->paymentAllocations = new ArrayCollection();

        foreach ($paymentAllocations as $allocation) {
            $this->addPaymentAllocation($allocation);
        }
    }

    /**
     * @return array
     */
    public function getPaymentAllocations()
    {
        return $this->paymentAllocations;
    }

    /**
     * @param PaymentAllocation $paymentAllocation
     */
    public function addPaymentAllocation(PaymentAllocation $paymentAllocation)
    {
        $paymentAllocation->setPayment($this);
        $this->paymentAllocations[] = $paymentAllocation;
    }

    /**
     * Computes rest
     */
    public function computeRest()
    {
        $rest = $this->amount;
        foreach ($this->getPaymentAllocations() as $allocation) {
            $rest -= $allocation->getAmount();
        }

        $this->rest = $rest;
    }
}
