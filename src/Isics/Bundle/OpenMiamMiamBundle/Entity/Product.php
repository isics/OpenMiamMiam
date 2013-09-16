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
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Isics\OpenMiamMiamBundle\Entity\Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\ProductRepository")
 */
class Product
{
    const AVAILABILITY_UNAVAILABLE        = 0;
    const AVAILABILITY_ACCORDING_TO_STOCK = 1;
    const AVAILABILITY_AVAILABLE_AT       = 2;
    const AVAILABILITY_AVAILABLE          = 3;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $category;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string $slug
     *
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=128, nullable=false, unique=true)
     */
    private $slug;

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
     */
    private $isBio;

    /**
     * @var boolean $isOfTheMoment
     *
     * @ORM\Column(name="is_of_the_moment", type="boolean", nullable=false)
     */
    private $isOfTheMoment;

    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=128, nullable=true)
     */
    private $image;

    /**
     * @var UploadedFile string
     */
    private $imageFile;

    /**
     * @var boolean $deleteImage
     */
    private $deleteImage;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

    /**
     * @var string $buyingUnit
     *
     * @ORM\Column(name="buying_unit", type="string", length=64, nullable=true)
     */
    private $buyingUnit;

    /**
     * @var boolean $allowDecimalQuantity
     *
     * @ORM\Column(name="allow_decimal_quantity", type="boolean", nullable=false)
     */
    private $allowDecimalQuantity;

    /**
     * @var decimal $price
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $price;

    /**
     * @var string $priceInfo
     *
     * @ORM\Column(name="price_info", type="string", length=128, nullable=true)
     */
    private $priceInfo;


    /**
     * @var integer $availability
     *
     * @ORM\Column(name="availability", type="integer", nullable=false)
     */
    private $availability;

    /**
     * @var decimal $stock
     *
     * @ORM\Column(name="stock", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $stock;

    /**
     * @var datetime $availableAt
     *
     * @ORM\Column(name="available_at", type="date", nullable=true)
     */
    private $availableAt;

    /**
     * @var Doctrine\Common\Collections\Collection $branches
     *
     * @ORM\ManyToMany(targetEntity="Branch", mappedBy="products")
     */
    private $branches;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->isBio                = false;
        $this->isOfTheMoment        = false;
        $this->allowDecimalQuantity = false;
        $this->availability         = self::AVAILABILITY_UNAVAILABLE;
        $this->deleteImage          = false;

        $this->branches = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Producer
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set ref
     *
     * @param string $ref
     * @return Product
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get ref
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set isBio
     *
     * @param boolean $isBio
     * @return Product
     */
    public function setIsBio($isBio)
    {
        $this->isBio = (bool)$isBio;

        return $this;
    }

    /**
     * Get isBio
     *
     * @return boolean
     */
    public function getIsBio()
    {
        return $this->isBio;
    }

    /**
     * Set isOfTheMoment
     *
     * @param boolean $isOfTheMoment
     * @return Product
     */
    public function setIsOfTheMoment($isOfTheMoment)
    {
        $this->isOfTheMoment = (bool)$isOfTheMoment;

        return $this;
    }

    /**
     * Get isOfTheMoment
     *
     * @return boolean
     */
    public function getIsOfTheMoment()
    {
        return $this->isOfTheMoment;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Product
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set image file
     *
     * @param UploadedFile $imageFile
     *
     * @return Product
     */
    public function setImageFile(UploadedFile $imageFile = null)
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    /**
     * Get image file
     *
     * @return UploadedFile
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set deleteImage flag
     *
     * @param boolean $deleteImage
     * @return Product
     */
    public function setDeleteImage($deleteImage)
    {
        $this->deleteImage = (bool)$deleteImage;

        return $this;
    }

    /**
     * Get deleteImage flag
     *
     * @return boolean
     */
    public function getDeleteImage()
    {
        return $this->deleteImage;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set buyingUnit
     *
     * @param string $buyingUnit
     * @return Product
     */
    public function setBuyingUnit($buyingUnit)
    {
        $this->buyingUnit = $buyingUnit;

        return $this;
    }

    /**
     * Get buyingUnit
     *
     * @return string
     */
    public function getBuyingUnit()
    {
        return $this->buyingUnit;
    }

    /**
     * Set allowDecimalQuantity
     *
     * @param boolean $allowDecimalQuantity
     * @return Product
     */
    public function setAllowDecimalQuantity($allowDecimalQuantity)
    {
        $this->allowDecimalQuantity = (bool)$allowDecimalQuantity;

        return $this;
    }

    /**
     * Get allowDecimalQuantity
     *
     * @return boolean
     */
    public function getAllowDecimalQuantity()
    {
        return $this->allowDecimalQuantity;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set priceInfo
     *
     * @param string $priceInfo
     * @return Product
     */
    public function setPriceInfo($priceInfo)
    {
        $this->priceInfo = $priceInfo;

        return $this;
    }

    /**
     * Get priceInfo
     *
     * @return string
     */
    public function getPriceInfo()
    {
        return $this->priceInfo;
    }

    /**
     * Set availability
     *
     * @param integer $availability
     * @return Product
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;

        return $this;
    }

    /**
     * Get availability
     *
     * @return integer
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Set stock
     *
     * @param float $stock
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock
     *
     * @return float
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set availableAt
     *
     * @param \DateTime $availableAt
     * @return Product
     */
    public function setAvailableAt($availableAt)
    {
        $this->availableAt = $availableAt;

        return $this;
    }

    /**
     * Get availableAt
     *
     * @return \DateTime
     */
    public function getAvailableAt()
    {
        return $this->availableAt;
    }

    /**
     * Set producer
     *
     * @param Producer $producer
     * @return Product
     */
    public function setProducer(Producer $producer)
    {
        $this->producer = $producer;

        return $this;
    }

    /**
     * Get producer
     *
     * @return Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Set category
     *
     * @param Category $category
     * @return Product
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add branch
     *
     * @param Branch $branch
     * @return Producer
     */
    public function addBranch(Branch $branch)
    {
        $this->branches[] = $branch;

        return $this;
    }

    /**
     * Remove branch
     *
     * @param Branch $branch
     */
    public function removeBranch(Branch $branch)
    {
        $this->branches->removeElement($branch);
    }

    /**
     * Get branches
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBranches()
    {
        return $this->branches;
    }

    /**
     * Returns true if product has branch
     *
     * @param Branch $branch
     *
     * @return boolean
     */
    public function hasBranch(Branch $branch)
    {
        foreach ($this->getBranches() as $_branch) {
            if ($_branch->getId() == $branch->getId()) {
                return true;
            }
        }

        return false;
    }
}
