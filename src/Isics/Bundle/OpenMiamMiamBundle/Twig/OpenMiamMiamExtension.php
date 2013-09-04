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

class OpenMiamMiamExtension extends \Twig_Extension
{
    /**
     * @var string $currency
     */
    private $currency;

    /**
     * Constructor
     */
    public function __construct($currency)
    {
        $this->currency = $currency;
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
