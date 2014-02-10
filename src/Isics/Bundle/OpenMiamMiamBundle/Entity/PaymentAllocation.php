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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;

/**
 * Isics\OpenMiamMiamBundle\Entity\PaymentAllocation
 *
 * @ORM\Table(name="payment_allocation")
 * @ORM\Entity
 */
class PaymentAllocation
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
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var \DateTime $date
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var payment
     *
     * @ORM\ManyToOne(targetEntity="Payment", inversedBy="paymentAllocations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=false, onDelete="RESTRICT")
     * })
     */
    private $payment;

    /**
     * @var salesOrder
     *
     * @ORM\ManyToOne(targetEntity="SalesOrder", inversedBy="paymentAllocations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sales_order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $salesOrder;



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
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\salesOrder $salesOrder
     */
    public function setSalesOrder(SalesOrder $salesOrder)
    {
        $this->salesOrder = $salesOrder;
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\salesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }
}
