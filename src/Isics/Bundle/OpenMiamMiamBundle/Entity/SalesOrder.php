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
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Isics\OpenMiamMiamBundle\Entity\SalesOrder
 *
 * @ORM\Table(name="sales_order")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SalesOrderRepository")
 */
class SalesOrder
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var BranchOccurrence
     *
     * @ORM\ManyToOne(targetEntity="BranchOccurrence")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="branch_occurrence_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $branchOccurrence;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="SalesOrderRow", mappedBy="salesOrder", cascade="all")
     */
    private $salesOrderRows;

    /**
     * @var string $ref
     *
     * @ORM\Column(name="ref", type="string", length=16, nullable=false)
     */
    private $ref;

    /**
     * @var \DateTime $date
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var integer $total
     *
     * @ORM\Column(name="total", type="integer", nullable=false)
     */
    private $total;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Isics\Bundle\OpenMiamMiamUserBundle\Entity\User", inversedBy="salesOrders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

    /**
     * @var string $firstname
     *
     * @ORM\Column(name="firstname", type="string", length=128, nullable=true)
     */
    private $firstname;

    /**
     * @var string $lastname
     *
     * @ORM\Column(name="lastname", type="string", length=128, nullable=true)
     */
    private $lastname;

    /**
     * @var string $address1
     *
     * @ORM\Column(name="address1", type="string", length=64, nullable=true)
     */
    private $address1;

    /**
     * @var string $address2
     *
     * @ORM\Column(name="address2", type="string", length=64, nullable=true)
     */
    private $address2;

    /**
     * @var string $zipcode
     *
     * @ORM\Column(name="zipcode", type="string", length=8, nullable=true)
     */
    private $zipcode;

    /**
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=64, nullable=true)
     */
    private $city;

    /**
     * @var string $consumerComment
     *
     * @ORM\Column(name="consumer_comment", type="string", length=255, nullable=true)
     */
    private $consumerComment;



    public function __construct()
    {
        $this->salesOrderRows = new ArrayCollection();
    }

    /**
     * @param string $consumerComment
     */
    public function setConsumerComment($consumerComment)
    {
        $this->consumerComment = $consumerComment;
    }

    /**
     * @return string
     */
    public function getConsumerComment()
    {
        return $this->consumerComment;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence $branchOccurrence
     */
    public function setBranchOccurrence($branchOccurrence)
    {
        $this->branchOccurrence = $branchOccurrence;
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence
     */
    public function getBranchOccurrence()
    {
        return $this->branchOccurrence;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
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
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
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
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param string $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param $salesOrderRows
     */
    public function setSalesOrderRows($salesOrderRows)
    {
        $this->salesOrderRows = new ArrayCollection();

        foreach ($salesOrderRows as $row) {
            $this->addSalesOrderRow($row);
        }
    }

    /**
     * @return array
     */
    public function getSalesOrderRows()
    {
        return $this->salesOrderRows;
    }

    /**
     * @param SalesOrderRow $row
     */
    public function addSalesOrderRow(SalesOrderRow $row)
    {
        $row->setSalesOrder($this);
        $this->salesOrderRows[] = $row;
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
     * Returns subtotal of sales order rows of a producer
     *
     * @param Producer $producer
     *
     * @return float
     */
    public function getSubTotalByProducer(Producer $producer)
    {
        $total = 0;
        foreach ($this->getSalesOrderRows() as $row) {
            if ($row->getProducer()->getId() == $producer->getId()) {
                $total += $row->getTotal();
            }
        }

        return $total;
    }
}
