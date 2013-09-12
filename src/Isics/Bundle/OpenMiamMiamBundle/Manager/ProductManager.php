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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

/**
 * Class ProductManager
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class ProductManager
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
     * Returns a new product for a producer
     *
     * @param Producer $producer
     *
     * @return Product
     */
    public function createForProducer(Producer $producer)
    {
        $product = new Product();
        $product->setProducer($producer);

        return $product;
    }

    /**
     * Saves a product
     *
     * @param Product $product
     */
    public function save(Product $product)
    {
        $this->objectManager->persist($product);
        $this->objectManager->flush();
    }

    /**
     * Deletes a product
     *
     * @param Product $product
     */
    public function delete(Product $product)
    {
        $this->objectManager->remove($product);
        $this->objectManager->flush();
    }
}
