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

/**
 * Isics\OpenMiamMiamBundle\Entity\AssociationHasProducer
 *
 * @ORM\Table(name="association_has_producer")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\AssociationHasProducerRepository")
 */
class AssociationHasProducer
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Producer $producer
     *
     * @ORM\ManyToOne(targetEntity="Producer", cascade={"all"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="producer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $producer;

    /**
     * @var Association $association
     *
     * @ORM\ManyToOne(targetEntity="Association")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="association_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $association;

    /**
     * @var float $commission
     *
     * @ORM\Column(name="commission", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $commission;


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
     * Set producer
     *
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\Producer $producer
     *
     * @return AssociationHasProducer
     */
    public function setProducer(Producer $producer)
    {
        $this->producer = $producer;

        return $this;
    }

    /**
     * Get producer
     *
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Set association
     *
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\Association $association
     *
     * @return AssociationHasProducer
     */
    public function setAssociation(Association $association)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\Association
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * Set commission
     *
     * @param float $commission
     *
     * @return AssociationHasProducer
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * Get commission
     *
     * @return float
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * Get commission on relation or default association commission if null
     *
     * @return int
     */
    public function getInheritedOrDefinedCommission()
    {
        if (null === $this->getCommission()) {
            return $this->getAssociation()->getDefaultCommission();
        }

        return $this->getCommission();
    }
}