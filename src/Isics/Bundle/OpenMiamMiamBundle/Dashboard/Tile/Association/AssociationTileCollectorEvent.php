<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Association;

use Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\TileCollectorEvent;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;

class AssociationTileCollectorEvent extends TileCollectorEvent
{
    /**
     * @var Association
     */
    protected $association;

    /**
     * @var BranchOccurrence
     */
    protected $branchOccurrence;

    /**
     * Constructor
     *
     * @param Association      $association
     * @param BranchOccurrence $branchOccurrence
     */
    public function __construct(Association $association, BranchOccurrence $branchOccurrence)
    {
        $this->association = $association;
        $this->branchOccurrence = $branchOccurrence;

        parent::__construct();
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\Association
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence
     */
    public function getBranchOccurrence()
    {
        return $this->branchOccurrence;
    }
}