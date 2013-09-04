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

/**
 * Isics\OpenMiamMiamBundle\Entity\Association
 *
 * @ORM\Table(name="association")
 * @ORM\Entity
 */
class Association
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false, unique=true)
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
     * @var string $presentation
     *
     * @ORM\Column(name="presentation", type="string", nullable=true)
     */
    private $presentation;

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
     * @var string $phone1
     *
     * @ORM\Column(name="phone1", type="string", length=16, nullable=true)
     */
    private $phone1;

    /**
     * @var string $phone2
     *
     * @ORM\Column(name="phone2", type="string", length=16, nullable=true)
     */
    private $phone2;

    /**
     * @var string $website
     *
     * @ORM\Column(name="website", type="string", length=128, nullable=true)
     */
    private $website;

    /**
     * @var string $facebook
     *
     * @ORM\Column(name="facebook", type="string", length=128, nullable=true)
     */
    private $facebook;

    /**
     * @var integer $orderRefCounter
     *
     * @ORM\Column(name="order_ref_counter", type="integer", nullable=false)
     */
    private $orderRefCounter;

    /**
     * @var integer $closingDelay
     *
     * @ORM\Column(name="closing_delay", type="integer", nullable=false)
     */
    private $closingDelay;

    /**
     * @var integer $openingDelay
     *
     * @ORM\Column(name="opening_delay", type="integer", nullable=false)
     */
    private $openingDelay;

   /**
     * @var decimal $defaultCommission
     *
     * @ORM\Column(name="default_commission", type="decimal", precision=5, scale=2, nullable=true)
     */
    private $defaultCommission;


    /**
     * @var Doctrine\Common\Collections\Collection $producers
     *
     * @ORM\ManyToMany(targetEntity="Producer", inversedBy="associations")
     * @ORM\JoinTable(name="association_has_producer",
     *   joinColumns={
     *     @ORM\JoinColumn(name="association_id", referencedColumnName="id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="producer_id", referencedColumnName="id", onDelete="CASCADE")
     *   }
     * )
     */
    private $producers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderRefCounter = 0;

        $this->producers = new ArrayCollection();
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
     * @return Association
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
     * @return Association
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
     * Set presentation
     *
     * @param string $presentation
     * @return Association
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;

        return $this;
    }

    /**
     * Get presentation
     *
     * @return string
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * Set address1
     *
     * @param string $address1
     * @return Association
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     * @return Association
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return Association
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Association
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set phone1
     *
     * @param string $phone1
     * @return Association
     */
    public function setPhone1($phone1)
    {
        $this->phone1 = $phone1;

        return $this;
    }

    /**
     * Get phone1
     *
     * @return string
     */
    public function getPhone1()
    {
        return $this->phone1;
    }

    /**
     * Set phone2
     *
     * @param string $phone2
     * @return Association
     */
    public function setPhone2($phone2)
    {
        $this->phone2 = $phone2;

        return $this;
    }

    /**
     * Get phone2
     *
     * @return string
     */
    public function getPhone2()
    {
        return $this->phone2;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return Association
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return Association
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set orderRefCounter
     *
     * @param integer $orderRefCounter
     * @return Association
     */
    public function setOrderRefCounter($orderRefCounter)
    {
        $this->orderRefCounter = $orderRefCounter;

        return $this;
    }

    /**
     * Get orderRefCounter
     *
     * @return integer
     */
    public function getOrderRefCounter()
    {
        return $this->orderRefCounter;
    }

    /**
     * Set closingDelay
     *
     * @param integer $closingDelay
     * @return Association
     */
    public function setClosingDelay($closingDelay)
    {
        $this->closingDelay = $closingDelay;

        return $this;
    }

    /**
     * Get closingDelay
     *
     * @return integer
     */
    public function getClosingDelay()
    {
        return $this->closingDelay;
    }

    /**
     * Set openingDelay
     *
     * @param integer $openingDelay
     * @return Association
     */
    public function setOpeningDelay($openingDelay)
    {
        $this->openingDelay = $openingDelay;

        return $this;
    }

    /**
     * Get openingDelay
     *
     * @return integer
     */
    public function getOpeningDelay()
    {
        return $this->openingDelay;
    }

    /**
     * Set defaultCommission
     *
     * @param float $defaultCommission
     * @return Association
     */
    public function setDefaultCommission($defaultCommission)
    {
        $this->defaultCommission = $defaultCommission;

        return $this;
    }

    /**
     * Get defaultCommission
     *
     * @return float
     */
    public function getDefaultCommission()
    {
        return $this->defaultCommission;
    }

    /**
     * Add producer
     *
     * @param Producer $producer
     * @return Association
     */
    public function addProducer(Producer $producer)
    {
        $this->producers[] = $producer;

        return $this;
    }

    /**
     * Remove producer
     *
     * @param Producer $producer
     */
    public function removeProducer(Producer $producer)
    {
        $this->producers->removeElement($producer);
    }

    /**
     * Get producers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducers()
    {
        return $this->producers;
    }
}