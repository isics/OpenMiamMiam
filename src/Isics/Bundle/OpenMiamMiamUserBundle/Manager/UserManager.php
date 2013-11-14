<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\Manager;


use Doctrine\ORM\EntityManager;

class UserManager
{
    /**
     * Number of days after his last order, a user is considered as a customer
     *
     * @var int $lastOrderNbDaysConsideringCustomer
     */
    private $lastOrderNbDaysConsideringCustomer;

    /**
     * Entity manager
     *
     * @var EntityManager $em
     */
    private $em;

    /**
     * Constructor
     *
     * @param int $last_order_nb_days_considering_customer
     */
    public function __construct(EntityManager $em, $lastOrderNbDaysConsideringCustomer)
    {
        $this->em = $em;
        $this->lastOrderNbDaysConsideringCustomer = $lastOrderNbDaysConsideringCustomer;
    }

    /**
     * Find consumers for branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     *
     * @return array Consumers
     */
    public function findConsumersForBranches($branches)
    {
        return $this->em->getRepository('IsicsOpenMiamMiamUserBundle:User')->findConsumersForBranches($branches, $this->lastOrderNbDaysConsideringCustomer);
    }
}
