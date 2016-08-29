<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard;

use Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Association\AssociationTileCollectorEvent;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchRepository;
use Isics\Bundle\OpenMiamMiamBundle\Manager\BranchOccurrenceManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssociationDashboardTilesBuilder
{
    /**
     * @var BranchOccurrenceManager
     */
    protected $branchOccurrenceManager;

    /**
     * @var BranchRepository
     */
    protected $branchRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param BranchOccurrenceManager  $branchOccurrenceManager
     * @param BranchRepository         $branchRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(BranchOccurrenceManager $branchOccurrenceManager, BranchRepository $branchRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->branchOccurrenceManager = $branchOccurrenceManager;
        $this->branchRepository        = $branchRepository;
        $this->eventDispatcher         = $eventDispatcher;
    }

    /**
     * Build tiles for association
     *
     * @param Association $association
     *
     * @return array
     */
    public function buildForAssociation(Association $association)
    {
        $data = array();

        $branches = $this->branchRepository
            ->filterAssociation($association)
            ->orderBy('b.city', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($branches as $branch) {
            $branchData = array();

            $branchData['branch'] = $branch;

            $branchOccurrence                = $this->branchOccurrenceManager->getNext($branch);
            $branchData['branch_occurrence'] = $branchOccurrence;

            // Planned dates
            if (null !== $branchOccurrence) {
                $event = new AssociationTileCollectorEvent($association, $branchOccurrence);

                $this->eventDispatcher->dispatch(
                    'open_miam_miam.admin.association.dashboard.collect_tiles',
                    $event
                );

                $branchData['tiles'] = $event->getTiles();
            }

            $data[] = $branchData;
        }

        return $data;
    }
}