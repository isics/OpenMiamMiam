<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class ArtificialProduct
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var Producer
     */
    protected $producer;


    public function setProducer(Producer $producer)
    {
        $this->producer = $producer;
    }

    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }
}
