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
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ConsumerManager;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ProducerManager;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ProductManager;
use Isics\Bundle\OpenMiamMiamBundle\Manager\BranchOccurrenceManager;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\Intl\Intl;

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
     * @var BranchOccurrenceManager $branchOccurrenceManager
     */
    private $branchOccurrenceManager;

    /**
     * Constructor
     *
     * @param string                  $title                   Title
     * @param string                  $currency                Currency
     * @param ProductManager          $productManager          Product manager
     * @param ProducerManager         $producerManager         Producer manager
     * @param ConsumerManager         $consumerManager         Consumer manager
     * @param BranchOccurrenceManager $branchOccurrenceManager BranchOccurrence manager
     */
    public function __construct($title,
                                $currency,
                                ProductManager $productManager,
                                ProducerManager $producerManager,
                                ConsumerManager $consumerManager,
                                BranchOccurrenceManager $branchOccurrenceManager)
    {
        // todo : decoupling extension
        $this->title                   = $title;
        $this->currency                = $currency;
        $this->productManager          = $productManager;
        $this->producerManager         = $producerManager;
        $this->consumerManager         = $consumerManager;
        $this->branchOccurrenceManager = $branchOccurrenceManager;
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
            new \Twig_SimpleFilter('format_currency_symbol', array($this, 'formatCurrencySymbol'))
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
            'get_producer_profile_image_path' => new \Twig_Function_Method($this, 'getProfileImageProducerPath'),
            'get_producer_presentation_image_path' => new \Twig_Function_Method($this, 'getPresentationImageProducerPath'),
            'get_products_to_display' => new \Twig_Function_Method($this, 'getProductsToDisplay'),
            'get_product_availability' => new \Twig_Function_Method($this, 'getProductAvailability')
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
     * Format currency symbol
     *
     * @param $currency
     */
    public function formatCurrencySymbol($currency)
    {
        return Intl::getCurrencyBundle()->getCurrencySymbol($currency, 'en');
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
     * Returns profile image producer path
     *
     * @param Producer $producer
     *
     * @return string
     */
    public function getProfileImageProducerPath(Producer $producer)
    {
        return $this->producerManager->getProfileImagePath($producer);
    }

    /**
     * Returns presentation image producer path
     *
     * @param Producer $producer
     *
     * @return string
     */
    public function getPresentationImageProducerPath(Producer $producer)
    {
        return $this->producerManager->getPresentationImagePath($producer);
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
     * Returns product availability
     *
     * @param BranchOccurrence $branchOccurrence
     * @param Product $product
     *
     * @return array
     */
    public function getProductAvailability(BranchOccurrence $branchOccurrence, Product $product)
    {
        return $this->branchOccurrenceManager->getProductAvailability($branchOccurrence, $product);
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
