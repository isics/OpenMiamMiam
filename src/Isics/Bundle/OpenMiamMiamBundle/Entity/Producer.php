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
 * Isics\OpenMiamMiamBundle\Entity\Producer
 *
 * @ORM\Table(name="producer")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\ProducerRepository")
 */
class Producer
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
     * @var integer $productRefCounter
     *
     * @ORM\Column(name="product_ref_counter", type="integer", nullable=false)
     */
    private $productRefCounter;

    /**
     * @var Doctrine\Common\Collections\Collection $associations
     *
     * @ORM\ManyToMany(targetEntity="Association", mappedBy="producers")
     */
    private $associations;

    /**
     * @var Doctrine\Common\Collections\Collection $branches
     *
     * @ORM\ManyToMany(targetEntity="Branch", mappedBy="producers")
     */
    private $branches;

    /**
     * @var string $profilImage
     *
     * @ORM\Column(name="profilImage", type="string", length=128, nullable=true)
     */
    private $profilImage;

    /**
     * @var UploadedFile string
     */
    private $profilImageFile;

    /**
     * @var boolean $deleteProfilImage
     */
    private $deleteProfilImage;
    
    /**
     * @var string $presentationImage
     *
     * @ORM\Column(name="presentationImage", type="string", length=128, nullable=true)
     */
    private $presentationImage;
    
    /**
     * @var UploadedFile string
     */
    private $presentationImageFile;
    
    /**
     * @var boolean $deletePresentationImage
     */
    private $deletePresentationImage;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productRefCounter = 0;

        $this->associations = new ArrayCollection();
        $this->branches     = new ArrayCollection();
        $this->deleteProfilImage = false;
        $this->deleteProducerImage = false;
    }

    public function __toString()
    {
        return $this->getName();
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
     * @return Producer
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
     * Set presentation
     *
     * @param string $presentation
     * @return Producer
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
     * @return Producer
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
     * @return Producer
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
     * @return Producer
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
     * @return Producer
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
     * @return Producer
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
     * @return Producer
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
     * @return Producer
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
     * @return Producer
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
     * Set productRefCounter
     *
     * @param integer $productRefCounter
     * @return Producer
     */
    public function setProductRefCounter($productRefCounter)
    {
        $this->productRefCounter = $productRefCounter;

        return $this;
    }

    /**
     * Get productRefCounter
     *
     * @return integer
     */
    public function getProductRefCounter()
    {
        return $this->productRefCounter;
    }

    /**
     * Add association
     *
     * @param Association $association
     * @return Producer
     */
    public function addAssociation(Association $association)
    {
        $this->associations[] = $association;

        return $this;
    }

    /**
     * Remove association
     *
     * @param Association $association
     */
    public function removeAssociation(Association $association)
    {
        $this->associations->removeElement($association);
    }

    /**
     * Get associations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Set ProfilImage
     *
     * @param string $profilImage
     * @return Product
     */
    public function setProfilImage($profilImage)
    {
        $this->profilImage = $profilImage;

        return $this;
    }

    /**
     * Get profilImage
     *
     * @return string
     */
    public function getProfilImage()
    {
        return $this->profilImage;
    }

    /**
     * Set ProfilImage file
     *
     * @param UploadedFile $profilImageFile
     *
     * @return Producer
     */
    public function setProfilImageFile(UploadedFile $profilImageFile = null)
    {
        $this->profilImageFile = $profilImageFile;

        return $this;
    }

    /**
     * Get profilImage file
     *
     * @return UploadedFile
     */
    public function getProfilImageFile()
    {
        return $this->profilImageFile;
    }

    /**
     * Set deleteProfilImage flag
     *
     * @param boolean $deleteImage
     * @return Producer
     */
    public function setDeleteProfilImage($deleteProfilImage)
    {
        $this->deleteProfilImage = (bool)$deleteProfilImage;

        return $this;
    }

    /**
     * Get deleteProfilImage flag
     *
     * @return boolean
     */
    public function getDeleteProfilImage()
    {
        return $this->deleteProfilImage;
    }

    /**
     * Set presentationImage
     *
     * @param string $presentationImage
     * @return Product
     */
    public function setPresentationImage($presentationImage)
    {
        $this->presentationImage = $presentationImage;
    
        return $this;
    }
    
    /**
     * Get presentationImage
     *
     * @return string
     */
    public function getPresentationImage()
    {
        return $this->presentationImage;
    }
    
    /**
     * Set prensentationImage file
     *
     * @param UploadedFile $presentationImageFile
     *
     * @return Producer
     */
    public function setPresentationImageFile(UploadedFile $presentationImageFile = null)
    {
        $this->presentationImageFile = $presentationImageFile;
    
        return $this;
    }
    
    /**
     * Get presentationImage file
     *
     * @return UploadedFile
     */
    public function getPresentationImageFile()
    {
        return $this->presentationImageFile;
    }
    
    /**
     * Set deletePresentationImage flag
     *
     * @param boolean $deletePresentationImage
     * @return Producer
     */
    public function setDeletePresentationImage($deletePresentationImage)
    {
        $this->deletePresentationImage = (bool)$deletePresentationImage;
    
        return $this;
    }
    
    /**
     * Get deletePresentationImage flag
     *
     * @return boolean
     */
    public function getDeletePresentationImage()
    {
        return $this->deletePresentationImage;
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
}
