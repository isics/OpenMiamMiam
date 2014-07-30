<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Producer;

use Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Tile;
use Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\TileCollection;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Symfony\Component\EventDispatcher\Event;

class ProducerTileCollectorEvent extends Event
{
    /**
     * @var Producer
     */
    protected $producer;

    /**
     * @var TileCollection
     */
    protected $tiles;

    /**
     * Constructor
     *
     * @param Producer         $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;

        $this->tiles = new TileCollection();
    }

    /**
     * @return \Isics\Bundle\OpenMiamMiamBundle\Entity\Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Add a tile in tiles collection
     *
     * @param Tile $tile
     */
    public function addTile(Tile $tile)
    {
        $this->tiles->addTile($tile);
    }

    /**
     * @return TileCollection
     */
    public function getTiles()
    {
        return $this->tiles;
    }
}