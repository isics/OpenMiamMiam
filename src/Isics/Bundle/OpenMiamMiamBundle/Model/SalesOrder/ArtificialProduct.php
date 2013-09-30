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
     * @var string
     */
    protected $ref;

    /**
     * @var decimal
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
     * @param decimal $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return decimal
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }
}
