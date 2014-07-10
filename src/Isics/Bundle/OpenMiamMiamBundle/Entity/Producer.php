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
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var integer $productRefCounter
     *
     * @ORM\Column(name="product_ref_counter", type="integer", nullable=false)
     */
    private $productRefCounter;

    /**
     * @var Doctrine\Common\Collections\Collection $associationHasProducer
     *
     * @ORM\OneToMany(targetEntity="AssociationHasProducer", mappedBy="producer", cascade={"all"}, orphanRemoval=true)
     */
    private $associationHasProducer;

    /**
     * @var Doctrine\Common\Collections\Collection $branches
     *
     * @ORM\ManyToMany(targetEntity="Branch", mappedBy="producers")
     */
    private $branches;

    /**
     * @var Doctrine\Common\Collections\Collection $producerAttendances
     *
     * @ORM\OneToMany(targetEntity="ProducerAttendance", mappedBy="producer")
     */
    private $producerAttendances;

    /**
     * @var string $image
     *
     * @ORM\Column(name="profile_image", type="string", length=128, nullable=true)
     */
    private $profileImage;

    /**
     * @var UploadedFile string
     */
    private $profileImageFile;

    /**
     * @var boolean $deleteProfileImage
     */
    private $deleteProfileImage;

    /**
     * @var string $presentationImage
     *
     * @ORM\Column(name="presentation_image", type="string", length=128, nullable=true)
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
     * @var Doctrine\Common\Collections\Collection $salesOrderRows
     *
     * @ORM\OneToMany(targetEntity="SalesOrderRow", mappedBy="producer")
     */
    private $salesOrderRows;

    /**
     * @var string $specialty
     *
     * @ORM\Column(name="specialty", type="string", length=32, nullable=true)
     */
    private $specialty;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productRefCounter = 0;

        $this->salesOrderRows = new ArrayCollection();
        $this->associationHasProducer = new ArrayCollection();
        $this->branches = new ArrayCollection();
        $this->deleteProfileImage = false;
        $this->deletePresentationImage = false;
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
     * Set phoneNumber1
     *
     * @param string $phoneNumber1
     * @return Producer
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
     * @return Producer
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Producer
     */
    public function setDeletedAt(\DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set productRefCounter
     *
     * @param integer $productRefCounter
     *
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
     *
     * @return Producer
     */
    public function addAssociation(Association $association)
    {
        if (!$this->hasAssociation($association)) {
            $associationHasProducer = new AssociationHasProducer();
            $associationHasProducer->setAssociation($association);
            $associationHasProducer->setProducer($this);
            $this->getAssociationHasProducer()->add($associationHasProducer);
            $association->getAssociationHasProducer()->add($associationHasProducer);
        }

        return $this;
    }

    /**
     * Remove association and related branches
     *
     * @param Association $association
     */
    public function removeAssociation(Association $association)
    {
        if ($this->hasAssociation($association)) {
            $this->getAssociationHasProducer()->removeElement(
                $this->getAssociationHasProducerByAssociation($association)
            );
        }

        foreach ($association->getBranches() as $branch) {
            $this->removeBranch($branch);
        }
    }

    /**
     * Get associations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociations()
    {
        $associations = new ArrayCollection();

        foreach($this->getAssociationHasProducer() as $associationHasProducer) {
            $associations->add($associationHasProducer->getAssociation());
        }

        return $associations;
    }

    /**
     * Get association has producer by association
     *
     * @param Association $association
     *
     * @return associationHasProducer|null
     */
    public function getAssociationHasProducerByAssociation(Association $association)
    {
        foreach($this->getAssociationHasProducer() as $associationHasProducer) {
            if (null !==  $association->getId()) {
                if ($associationHasProducer->getAssociation()->getId() === $association->getId()) {
                    return $associationHasProducer;
                }
            }
            elseif ($associationHasProducer->getAssociation() === $association) {
                return $associationHasProducer;
            }
        }

        return null;
    }

    /**
     * Returns true if producer has association
     *
     * @param Association $association
     * @return bool
     */
    public function hasAssociation(Association $association)
    {
        return null !== $this->getAssociationHasProducerByAssociation($association);
    }

    /**
     * Set ProfileImage
     *
     * @param string $profileImage
     * @return Product
     */
    public function setProfileImage($profileImage)
    {
        $this->profileImage = $profileImage;

        return $this;
    }

    /**
     * Get profileImage
     *
     * @return string
     */
    public function getProfileImage()
    {
        return $this->profileImage;
    }

    /**
     * Set profile image file
     *
     * @param UploadedFile $profileImageFile
     *
     * @return Producer
     */
    public function setProfileImageFile(UploadedFile $profileImageFile = null)
    {
        $this->profileImageFile = $profileImageFile;

        return $this;
    }

    /**
     * Get profileImage file
     *
     * @return UploadedFile
     */
    public function getProfileImageFile()
    {
        return $this->profileImageFile;
    }

    /**
     * Set deleteProfileImage flag
     *
     * @param boolean $deleteProfileImage
     *
     * @return Producer
     */
    public function setDeleteProfileImage($deleteProfileImage)
    {
        $this->deleteProfileImage = (bool)$deleteProfileImage;

        return $this;
    }

    /**
     * Get deleteProfileImage flag
     *
     * @return boolean
     */
    public function getDeleteProfileImage()
    {
        return $this->deleteProfileImage;
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
     * Set prensentation image file
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
        $branch->addProducer($this);

        return $this;
    }

    /**
     * Returns true if producer has branch
     *
     * @param Branch $branch
     *
     * @return bool
     */
    public function hasBranch(Branch $branch)
    {
        foreach ($this->getBranches() as $_branch) {
            if ($branch->getId() == $_branch->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove branch
     *
     * @param Branch $branch
     */
    public function removeBranch(Branch $branch)
    {
        $this->branches->removeElement($branch);
        $branch->removeProducer($this);
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
     * Add producer attendance
     *
     * @param ProducerAttendance $producerAttendance
     * @return BranchOccurrence
     */
    public function addProducerAttendance(ProducerAttendance $producerAttendance)
    {
        $this->producerAttendances[] = $producerAttendance;

        return $this;
    }

    /**
     * Remove producera ttendance
     *
     * @param ProducerAttendance $producerAttendance
     */
    public function removeProducerAttendance(ProducerAttendance $producerAttendance)
    {
        $this->producerAttendances->removeElement($producerAttendance);
    }

    /**
     * Get producer attendances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducerAttendances()
    {
        return $this->producerAttendances;
    }

    /**
     * Set association_has_producer
     *
     * @param \Doctrine\Common\Collections\Collection $associationHasProducer
     */
    public function setAssociationHasProducer($associationHasProducer)
    {
        $this->associationHasProducer = $associationHasProducer;

        return $this;
    }

    /**
     * Get association_has_producer
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociationHasProducer()
    {
        return $this->associationHasProducer;
    }

    /**
     * Set sales_order_rows
     *
     * @param \Doctrine\Common\Collections\Collection $salesOrderRows
     */
    public function setSalesOrderRows($salesOrderRows)
    {
        $this->salesOrderRows = $salesOrderRows;
    }

    /**
     * Get sales_order_rows
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSalesOrderRows()
    {
        return $this->salesOrderRows;
    }

    /**
     * Set specialty
     *
     * @param $specialty
     */
    public function setSpecialty($specialty)
    {
        $this->specialty = $specialty;
    }

    /**
     * Get specialty
     *
     * @return string
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }
}
