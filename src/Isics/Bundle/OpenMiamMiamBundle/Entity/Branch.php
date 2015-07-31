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
 * Isics\OpenMiamMiamBundle\Entity\Branch
 *
 * @ORM\Table(name="branch")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchRepository")
 */
class Branch
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
     * @var Association
     *
     * @ORM\ManyToOne(targetEntity="Association", inversedBy="branches")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="association_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $association;

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
     * @var string $welcomeText
     *
     * @ORM\Column(name="welcome_text", type="text", nullable=true)
     */
    private $welcomeText;

    /**
     * @var string $presentation
     *
     * @ORM\Column(name="presentation", type="text", nullable=true)
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
     * @var string $phoneNumber1
     *
     * @ORM\Column(name="phone_number1", type="string", length=16, nullable=true)
     */
    private $phoneNumber1;

    /**
     * @var string $phoneNumber2
     *
     * @ORM\Column(name="phone_number2", type="string", length=16, nullable=true)
     */
    private $phoneNumber2;

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
     * @var \Doctrine\Common\Collections\Collection $associationProducers
     *
     * @ORM\ManyToMany(targetEntity="AssociationHasProducer", inversedBy="branches")
     * @ORM\JoinTable(name="branch_has_association_producer",
     *   joinColumns={
     *     @ORM\JoinColumn(name="branch_id", referencedColumnName="id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="association_producer_id", referencedColumnName="id", onDelete="CASCADE")
     *   }
     * )
     */
    private $associationProducers;

    /**
     * @var Doctrine\Common\Collections\Collection $products
     *
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="branches")
     */
    private $products;

    /**
     * @var Doctrine\Common\Collection\Collection $articles
     *
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="branches")
     */
    private $articles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->producers = new ArrayCollection();
        $this->products  = new ArrayCollection();
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
     * @return Branch
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
     * Get name with association
     *
     * @return string
     */
    public function getNameWithAssociation()
    {
        return $this->getAssociation()->getName().' / '.$this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Branch
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
     * Set homepage presentation
     *
     * @param string $welcomeText
     * @return Branch
     */
    public function setWelcomeText($welcomeText)
    {
        $this->welcomeText = $welcomeText;

        return $this;
    }

    /**
     * Get homepage presentation
     *
     * @return string
     */
    public function getWelcomeText()
    {
        return $this->welcomeText;
    }

    /**
     * Set presentation
     *
     * @param string $presentation
     * @return Branch
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
     * @return Branch
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
     * @return Branch
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
     * @return Branch
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
     * @return Branch
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
     * Set phoneNumber1
     *
     * @param string $phoneNumber1
     * @return Branch
     */
    public function setPhoneNumber1($phoneNumber1)
    {
        $this->phoneNumber1 = $phoneNumber1;

        return $this;
    }

    /**
     * Get phoneNumber1
     *
     * @return string
     */
    public function getPhoneNumber1()
    {
        return $this->phoneNumber1;
    }

    /**
     * Set phoneNumber2
     *
     * @param string $phoneNumber2
     * @return Branch
     */
    public function setPhoneNumber2($phoneNumber2)
    {
        $this->phoneNumber2 = $phoneNumber2;

        return $this;
    }

    /**
     * Get phoneNumber2
     *
     * @return string
     */
    public function getPhoneNumber2()
    {
        return $this->phoneNumber2;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return Branch
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
     * @return Branch
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
     * Set association
     *
     * @param Association $association
     * @return Branch
     */
    public function setAssociation(Association $association)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return Association
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * Add AssociationHasProducer
     *
     * @param AssociationHasProducer $associationHasProducer
     *
     * @return $this
     */
    public function addAssociationProducer(AssociationHasProducer $associationHasProducer)
    {
        $this->associationProducers[] = $associationHasProducer;

        return $this;
    }

    /**
     * Remove AssociationHasProducer
     *
     * @param AssociationHasProducer $associationHasProducer
     */
    public function removeAssociationProducer(AssociationHasProducer $associationHasProducer)
    {
        $this->associationProducers->removeElement($associationHasProducer);
    }

    /**
     * Get AssociationProducers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociationProducers()
    {
        return $this->associationProducers;
    }

    /**
     * Add product
     *
     * @param Product $product
     * @return Branch
     */
    public function addProduct(Product $product)
    {
        $product->addBranch($this);
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param Product $product
     */
    public function removeProduct(Product $product)
    {
        $product->removeBranch($this);
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Doctrine\Common\Collection\Collection $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     * @return Doctrine\Common\Collection\Collection
     */
    public function getArticles()
    {
        return $this->articles;
    }
}
