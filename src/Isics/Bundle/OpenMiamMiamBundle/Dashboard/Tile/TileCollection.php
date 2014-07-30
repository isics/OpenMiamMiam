<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile;

class TileCollection implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $tiles;

    /**
     * Constructor
     *
     * @param array $tiles
     */
    public function __construct(array $tiles = array())
    {
        $this->setTiles($tiles);
    }

    /**
     * @param mixed $tiles
     */
    public function setTiles($tiles)
    {
        $this->tiles = array();

        foreach ($tiles as $tile) {
            $this->addTile($tile);
        }
    }

    /**
     * @return mixed
     */
    public function getTiles()
    {
        return $this->tiles;
    }

    /**
     * Add tile
     *
     * @param Tile $tile
     */
    public function addTile(Tile $tile)
    {
        $this->tiles[] = $tile;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->tiles);
    }
}