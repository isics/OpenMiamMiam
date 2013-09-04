<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

class LoadProductData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $product = new Product();
        $product->setName('Panier de légumes');
        $product->setProducer($this->getReference('producer Beth Rave'));
        $product->setCategory($this->getReference('category Fruits et Légumes'));
        $product->setPrice(15);
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);
        $this->getReference('branch Ipsum')->addProduct($product);

        $product = new Product();
        $product->setName('Côte de bœuf');
        $product->setProducer($this->getReference('producer Elsa Dorsa'));
        $product->setCategory($this->getReference('category Viande'));
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE_AT);
        $product->setAvailableAt(new \DateTime('next month'));
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);

        $product = new Product();
        $product->setName('Merguez');
        $product->setProducer($this->getReference('producer Elsa Dorsa'));
        $product->setCategory($this->getReference('category Viande'));
        $product->setDescription('100% agneau');
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);

        $product = new Product();
        $product->setName('Beurre');
        $product->setProducer($this->getReference('producer Roméo Frigo'));
        $product->setCategory($this->getReference('category Laitages'));
        $product->setPrice(.4);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(14);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);
        $this->getReference('branch Ipsum')->addProduct($product);

        $product = new Product();
        $product->setName('Yahourt nature');
        $product->setProducer($this->getReference('producer Roméo Frigo'));
        $product->setCategory($this->getReference('category Laitages'));
        $product->setPrice(.5);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(0);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);
        $this->getReference('branch Ipsum')->addProduct($product);

        $product = new Product();
        $product->setName('Yahourt aux fruits');
        $product->setProducer($this->getReference('producer Roméo Frigo'));
        $product->setCategory($this->getReference('category Laitages'));
        $product->setPrice(.6);
        $product->setAvailability(Product::AVAILABILITY_UNAVAILABLE);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);
        $this->getReference('branch Ipsum')->addProduct($product);

        $manager->persist($this->getReference('branch Lorem'));
        $manager->persist($this->getReference('branch Ipsum'));

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6;
    }
}