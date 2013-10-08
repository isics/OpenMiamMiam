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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Isics\OpenMiamMiamBundle\Entity\SalesOrderRow
 *
 * @ORM\Table(name="sales_order_row")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class SalesOrderRow
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
     * @var SalesOrder
     *
     * @ORM\ManyToOne(targetEntity="SalesOrder", inversedBy="salesOrderRows")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sales_order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $salesOrder;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    private $product;

    /**
     * @var Producer
     *
     * @ORM\ManyToOne(targetEntity="Producer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="producer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $producer;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string $ref
     *
     * @ORM\Column(name="ref", type="string", length=16, nullable=false)
     */
    private $ref;

    /**
     * @var boolean $isBio
     *
     * @ORM\Column(name="is_bio", type="boolean", nullable=false)
     * @todo rename to "isOrganicFood"
     */
    private $isBio;

    /**
     * @var float
     *
     * @ORM\Column(name="unit_price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $unitPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $quantity;

    /**
     * @var float
     */
    private $oldQuantity;

    /**
     * @var float $total
     *
     * @ORM\Column(name="total", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $total;



    /**
     * Constructs object
     */
    public function __construct()
    {
        $this->total = 0;
        $this->quantity = 0;
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
     * @param Product $product
     */
    public function setProduct(Product $product = null)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Producer $producer
     */
    public function setProducer(Producer $producer = null)
    {
        $this->producer = $producer;
    }

    /**
     * @return Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * @param float
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getOldQuantity()
    {
        return $this->oldQuantity;
    }

    /**
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder $salesOrder
     */
    public function setSalesOrder($salesOrder)
    {
        $this->salesOrder = $salesOrder;
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $unitPrice
     */
    public function setUnitPrice($unitPrice = null)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param boolean $isBio
     */
    public function setIsBio($isBio)
    {
        $this->isBio = $isBio;
    }

    /**
     * @return boolean
     */
    public function getIsBio()
    {
        return $this->isBio;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @ORM\PostLoad()
     */
    public function postLoad()
    {
        $this->oldQuantity = $this->quantity;
    }
}
