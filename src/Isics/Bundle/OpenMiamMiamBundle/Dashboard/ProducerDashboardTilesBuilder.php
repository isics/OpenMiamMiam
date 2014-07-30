<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard;

use Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Producer\ProducerTileCollectorEvent;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProducerDashboardTilesBuilder
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns tiles for producer dashboard
     *
     * @param Producer         $producer
     * @param BranchOccurrence $branchOccurrence
     *
     * @return Tile\TileCollection
     */
    public function buildForProducer(Producer $producer)
    {
        $event = new ProducerTileCollectorEvent($producer);

        $this->eventDispatcher->dispatch('open_miam_miam.admin.producer.dashboard.collect_tiles', $event);

        return $event->getTiles();
    }
}