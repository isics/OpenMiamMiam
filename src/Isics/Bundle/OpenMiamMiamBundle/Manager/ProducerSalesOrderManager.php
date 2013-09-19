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

use Doctrine\Common\Persistence\ObjectManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

/**
 * Class ProducerSalesOrderManager
 * Manager for sales order of a producer
 */
class ProducerSalesOrderManager
{
    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * Constructs object
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns sales order of a producer for next branch occurrences
     *
     * @param Producer $producer
     *
     * @return array
     */
    public function getForNextBranchOccurrences(Producer $producer)
    {
        $branchOccurrenceRepository = $this->objectManager->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence');
        $salesOrderRepository = $this->objectManager->getRepository('IsicsOpenMiamMiamBundle:SalesOrder');

        foreach ($producer->getBranches() as $branch) {
            $branchOccurrences = $branchOccurrenceRepository->findOneNextForBranch($branch, true);
            $salesOrder = $salesOrderRepository->findBy(array('producer' => $producer, 'branchOccurrences' => $branchOccurrences));
        }


    }
}
