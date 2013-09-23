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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadProductData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $productManager = $this->container->get('open_miam_miam.product_manager');

        $product = $productManager->createForProducer($this->getReference('Beth Rave'));
        $product->setName('Basket of vegetables');
        $product->setCategory($this->getReference('Fruits and vegetables'));
        $product->setPrice(15);
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('Elsa Dorsa'));
        $product->setName('Prime rib of beef');
        $product->setCategory($this->getReference('Beef'));
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE_AT);
        $product->setAvailableAt(new \DateTime('next month'));
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('Elsa Dorsa'));
        $product->setName('Sausages');
        $product->setCategory($this->getReference('Pork'));
        $product->setDescription('100% lamb');
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('Romeo Frigo'));
        $product->setName('Butter');
        $product->setCategory($this->getReference('Dairy produce'));
        $product->setPrice(.4);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(14);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('Romeo Frigo'));
        $product->setName('Plain yoghurt ');
        $product->setCategory($this->getReference('Dairy produce'));
        $product->setPrice(.5);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(0);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('Romeo Frigo'));
        $product->setName('Fruit yoghurt');
        $product->setCategory($this->getReference('Dairy produce'));
        $product->setPrice(.6);
        $product->setAvailability(Product::AVAILABILITY_UNAVAILABLE);
        $productManager->save($product);

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
