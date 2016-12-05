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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LoadProductData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
        $this->translator->setLocale($this->container->getParameter('locale'));
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $productManager = $this->container->get('open_miam_miam.product_manager');

        $product = $productManager->createForProducer($this->getReference('producer.beth_rave'));
        $product->setName($this->translator->trans('product.basket_of_vegetables', array(), 'fixtures'));
        $product->setCategory($this->getReference('category.fruits_and_vegetables'));
        $product->setPrice(15);
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('producer.elsa_dorsa'));
        $product->setName($this->translator->trans('product.prime_rib_of_beef', array(), 'fixtures'));
        $product->setCategory($this->getReference('category.beef'));
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE_AT);
        $product->setAvailableAt(new \DateTime('next month'));
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('producer.elsa_dorsa'));
        $product->setName($this->translator->trans('product.sausages', array(), 'fixtures'));
        $product->setCategory($this->getReference('category.pork'));
        $product->setDescription('100% lamb');
        $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('producer.romeo_frigo'));
        $product->setName($this->translator->trans('product.butter', array(), 'fixtures'));
        $product->setCategory($this->getReference('category.dairy_produce'));
        $product->setPrice(.4);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(14);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('producer.romeo_frigo'));
        $product->setName($this->translator->trans('product.sugar', array(), 'fixtures'));
        $product->setCategory($this->getReference('category.dairy_produce'));
        $product->setAllowDecimalQuantity(true);
        $product->setPrice(1);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(14);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('producer.romeo_frigo'));
        $product->setName($this->translator->trans('product.plain_yoghurt', array(), 'fixtures'));
        $product->setCategory($this->getReference('category.dairy_produce'));
        $product->setPrice(.5);
        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock(0);
        $productManager->save($product);

        $product = $productManager->createForProducer($this->getReference('producer.romeo_frigo'));
        $product->setName($this->translator->trans('product.fruit_yoghurt', array(), 'fixtures'));
        $product->setCategory($this->getReference('category.dairy_produce'));
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
