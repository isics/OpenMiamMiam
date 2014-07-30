<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile;

use Symfony\Component\EventDispatcher\Event;

class TileCollectorEvent extends Event
{
    /**
     * @var TileCollection
     */
    protected $tiles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tiles = new TileCollection();
    }

    /**
     * Add a tile on the event
     *
     * @param Tile $tile
     */
    public function addTile(Tile $tile)
    {
        $this->tiles->addTile($tile);
    }

    /**
     * Return event tiles
     *
     * @return TileCollection
     */
    public function getTiles()
    {
        return $this->tiles;
    }
}