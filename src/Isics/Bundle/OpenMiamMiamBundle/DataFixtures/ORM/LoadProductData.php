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
        // TODO : traduire les fixtures

        $productManager = $this->container->get('open_miam_miam.product_manager');

        $product = $productManager->createForProducer($this->getReference('producer Beth Rave'));
        $product->setName('Panier de légumes');
        $product->setCategory($this->getReference('category Fruits et Légumes'));
        $product->setPrice(15);
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);
        $this->getReference('branch Ipsum')->addProduct($product);

        $product = $productManager->createForProducer($this->getReference('producer Elsa Dorsa'));
        $product->setName('Côte de bœuf');
        $product->setCategory($this->getReference('category Viande'));
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE_AT);
        $product->setAvailableAt(new \DateTime('next month'));
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);

        $product = $productManager->createForProducer($this->getReference('producer Elsa Dorsa'));
        $product->setName('Merguez');
        $product->setCategory($this->getReference('category Viande'));
        $product->setDescription('100% agneau');
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);

        $product = $productManager->createForProducer($this->getReference('producer Roméo Frigo'));
        $product->setName('Beurre');
        $product->setCategory($this->getReference('category Laitages'));
        $product->setPrice(.4);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(14);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);
        $this->getReference('branch Ipsum')->addProduct($product);

        $product = $productManager->createForProducer($this->getReference('producer Roméo Frigo'));
        $product->setName('Yahourt nature');
        $product->setCategory($this->getReference('category Laitages'));
        $product->setPrice(.5);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(0);
        $manager->persist($product);
        $this->getReference('branch Lorem')->addProduct($product);
        $this->getReference('branch Ipsum')->addProduct($product);

        $product = $productManager->createForProducer($this->getReference('producer Roméo Frigo'));
        $product->setName('Yahourt aux fruits');
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
