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

use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Isics\OpenMiamMiamBundle\Entity\Association
 *
 * @ORM\Table(name="association")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\AssociationRepository")
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
     * @var float $defaultCommission
     *
     * @ORM\Column(name="default_commission", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $defaultCommission;

    /**
     * @var \Doctrine\Common\Collections\Collection $branches
     *
     * @ORM\OneToMany(targetEntity="Branch", mappedBy="association")
     */
     private $branches;

    /**
     * @var \Doctrine\Common\Collections\Collection $associationHasProducer
     *
     * @ORM\OneToMany(targetEntity="AssociationHasProducer", mappedBy="association", cascade={"persist"})
     */
    private $associationHasProducer;

    /**
     * @var \Doctrine\Common\Collections\Collection $subscriptions
     *
     * @ORM\OneToMany(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\Subscription", mappedBy="association")
     */
    private $subscriptions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderRefCounter = 0;

        $this->associationHasProducer = new ArrayCollection();
        $this->branches = new ArrayCollection();
    }

    /**
     * Returns a string representation
     *
     * @return string
     */
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     * Set phoneNumber1
     *
     * @param string $phoneNumber1
     *
     * @return Association
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
     *
     * @return Association
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
        if (!$this->hasProducer($producer)) {
            $associationHasProducer = new AssociationHasProducer();
            $associationHasProducer->setAssociation($this);
            $associationHasProducer->setProducer($producer);
            $this->associationHasProducer->add($associationHasProducer);
            $producer->getAssociationHasProducer()->add($associationHasProducer);
        }

        return $this;
    }

    /**
     * Remove producer
     *
     * @param Producer $producer
     */
    public function removeProducer(Producer $producer)
    {
        if ($this->hasProducer($producer)) {
            $this->getAssociationHasProducer()->removeElement(
                $this->getAssociationHasProducerByProducer($producer)
            );
        }
    }

    /**
     * Get producers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducers()
    {
        $producers = new ArrayCollection();

        foreach($this->getAssociationHasProducer() as $associationHasProducer) {
            $producers->add($associationHasProducer->getProducer());
        }

        return $producers;
    }

    /**
     * Get association has producer by producer
     *
     * @param Producer $producer
     * @return null
     */
    public function getAssociationHasProducerByProducer(Producer $producer)
    {
        foreach($this->getAssociationHasProducer() as $associationHasProducer) {
            if (null !==  $producer->getId()) {
                if ($associationHasProducer->getProducer()->getId() === $producer->getId()) {
                    return $associationHasProducer;
                }
            }
            elseif ($associationHasProducer->getProducer() === $producer) {
                return $associationHasProducer;
            }
        }

        return null;
    }

    /**
     * Returns true if association has producer
     *
     * @param Producer $producer
     * @return bool
     */
    public function hasProducer(Producer $producer)
    {
        return null !== $this->getAssociationHasProducerByProducer($producer);
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
     * Add branch
     *
     * @param Branch $branch
     * @return Association
     */
    public function addBranch(Branch $branch)
    {
        $branch->setAssociation($this);
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
     * Set branches
     *
     * @param mixed $branches
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setBranches($branches)
    {
        $this->branches = new ArrayCollection();
        foreach ($branches as $branch) {
            $this->addBranch($branch);
        }

        return $this;
    }

    /**
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * Return subscription for association
     *
     * @param User $user
     *
     * @return Subscription
     */
    public function getSubscriptionForUser(User $user = null)
    {
        foreach ($this->subscriptions as $subcription) {
            if ((null === $user && null === $subcription->getUser())
                || (null !== $user && null !== $subcription->getUser() && $subcription->getUser()->getId() == $user->getId())) {
                return $subcription;
            }
        }

        return null;
    }
}
