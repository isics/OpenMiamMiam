<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile;

class Tile
{
    /**
     * @var string
     */
    protected $iconClass;

    /**
     * @var string
     */
    protected $tileClass;

    /**
     * @var string
     */
    protected $header;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $link;

    /**
     * Constructor
     *
     * @param string $value
     * @param string $description
     * @param string $iconClass
     * @param string $tileClass
     * @param string $header
     * @param string $link
     */
    public function __construct($value = null, $description = null, $iconClass = null, $tileClass = null, $header = null, $link = null)
    {
        $this->value       = $value;
        $this->description = $description;
        $this->iconClass   = $iconClass;
        $this->tileClass   = $tileClass;
        $this->header      = $header;
        $this->link        = $link;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $iconClass
     */
    public function setIconClass($iconClass)
    {
        $this->iconClass = $iconClass;
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return $this->iconClass;
    }

    /**
     * @param string $tileClass
     */
    public function setTileClass($tileClass)
    {
        $this->tileClass = $tileClass;
    }

    /**
     * @return string
     */
    public function getTileClass()
    {
        return $this->tileClass;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
}