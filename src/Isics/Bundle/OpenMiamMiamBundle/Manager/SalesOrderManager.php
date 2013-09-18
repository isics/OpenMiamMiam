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

use Isics\Bundle\OpenMiamMiamBundle\Model\Cart;

/**
 * Class SalesOrderManager
 * Manager for sales order
 */
class SalesOrderManager
{
    /**
     * Creates sales order from a cart
     *
     * @param Cart $cart
     *
     * @return SalesOrder
     */
    public function createFromCart(Cart $cart)
    {
    }
}
