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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ConsumerManager;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ProductManager;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class OpenMiamMiamExtension extends \Twig_Extension
{
    /**
     * @var string $currency
     */
    private $currency;

    /**
     * @var ProductManager $productManager
     */
    private $productManager;

    /**
     * @var ConsumerManager $consumerManager
     */
    private $consumerManager;

    /**
     * Constructor
     */
    public function __construct($currency, ProductManager $productManager, ConsumerManager $consumerManager)
    {
        $this->currency        = $currency;
        $this->productManager  = $productManager;
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
            'get_image_product_path' => new \Twig_Function_Method($this, 'getImageProductPath')
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
     * Returns globals
     *
     * @return array
     */
    public function getGlobals()
    {
        return array(
            'open_miam_miam' => array(
                'currency' => $this->currency
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
