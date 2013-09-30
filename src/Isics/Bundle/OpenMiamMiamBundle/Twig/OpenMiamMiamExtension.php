<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Twig;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ConsumerManager;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ProductManager;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ProducerManager;

class OpenMiamMiamExtension extends \Twig_Extension
{
    /**
     * @var string $title
     */
    private $title;

    /**
     * @var string $currency
     */
    private $currency;

    /**
     * @var ProductManager $productManager
     */
    private $productManager;

    /**
     * @var ProducerManager $producerManager
     */
    private $producerManager;

    /**
     * @var ConsumerManager $consumerManager
     */
    private $consumerManager;

    /**
     * Constructor
     *
     * @param string          $title           Title
     * @param string          $currency        Currency
     * @param ProductManager  $productManager  Product manager
     * @param ProducerManager $produerManager  Producer manager
     * @param ConsumerManager $consumerManager Consumer manager
     */
    public function __construct($title, $currency, ProductManager $productManager, ProducerManager $producerManager, ConsumerManager $consumerManager)
    {
        $this->title           = $title;
        $this->currency        = $currency;
        $this->productManager  = $productManager;
        $this->producerManager = $producerManager;
        $this->consumerManager = $consumerManager;
    }

    /**
     * Returns available filters
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('format_consumer_ref', array($this, 'formatConsumerRef')),
        );
    }

    /**
     * Returns available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'get_image_product_path' => new \Twig_Function_Method($this, 'getImageProductPath'),
            'get_image_producer_path' => new \Twig_Function_Method($this, 'getImageProducerPath'),
            'get_products_to_display' => new \Twig_Function_Method($this, 'getProductsToDisplay')
        );
    }

    /**
     * Format consumer ref
     *
     * @param User $user
     *
     * @return string
     */
    public function formatConsumerRef(User $user)
    {
        return $this->consumerManager->formatRef($user);
    }

    /**
     * Returns image product path
     *
     * @param Product $product
     *
     * @return string
     */
    public function getImageProductPath(Product $product)
    {
        return $this->productManager->getImagePath($product);
    }

    /**
     * Returns image producer path
     *
     * @param Producer $producer
     *
     * @return string
     */
    public function getImageProducerPath(Producer $producer)
    {
        return $this->producerManager->getImagePath($producer);
    }

    /**
     * Returns products to display
     *
     * @param Branch $branch
     * @param Category $category
     *
     * @return array
     */
    public function getProductsToDisplay(Branch $branch, Category $category)
    {
        return $this->productManager->getProductsToDisplay($branch, $category);
    }

    /**
     * Returns globals
     *
     * @return array
     */
    public function getGlobals()
    {
        return array(
            'open_miam_miam' => array(
                'title'    => $this->title,
                'currency' => $this->currency,
        ));
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'open_miam_miam_extension';
    }
}
