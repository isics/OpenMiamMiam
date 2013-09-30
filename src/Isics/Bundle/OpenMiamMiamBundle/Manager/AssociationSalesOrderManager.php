<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;


/**
 * Class AssociationSalesOrderManager
 * Manager for sales order of an association
 */
class AssociationSalesOrderManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs object
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns sales orders for branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     *
     * @return array
     */
    public function getForBranchOccurrence(BranchOccurrence $branchOccurrence)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->findForBranchOccurrence($branchOccurrence);
    }
}
