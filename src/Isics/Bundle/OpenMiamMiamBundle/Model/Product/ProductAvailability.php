<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Product;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

class ProductAvailability
{
    const REASON_AVAILABLE                 = 0;
    const REASON_IN_STOCK                  = 1;
    const REASON_UNAVAILABLE               = 2;
    const REASON_NO_NEXT_BRANCH_OCCURRENCE = 3;
    const REASON_PRODUCER_ABSENT           = 4;
    const REASON_OUT_OF_STOCK              = 5;
    const REASON_AVAILABLE_AT              = 6;

    /**
     * @var integer $reason
     */
    protected $reason;

    /**
     * @var Product $product
     */
    protected $product;



    /**
     * Constructs object
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Gets reason
     *
     * @return integer Reason
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Sets reason
     *
     * @param integer $reason Reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * Gets product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Returns true if product is available
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return in_array($this->reason, array(self::REASON_AVAILABLE, self::REASON_IN_STOCK));
    }
}
