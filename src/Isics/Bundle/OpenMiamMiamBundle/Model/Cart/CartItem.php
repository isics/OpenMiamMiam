<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Cart;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart\Cart;

class CartItem implements \Serializable
{
    /**
     * @var Cart $cart
     */
    protected $cart;

    /**
     * @var integer $productId
     */
    protected $productId;

    /**
     * @var Product $product
     */
    protected $product;

    /**
     * @var decimal $quantity
     */
    protected $quantity;

    /**
     * @var decimal $total
     */
    protected $total;

    /**
     * Returns cart
     *
     * @return cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Sets cart
     *
     * @param Cart $cart Cart
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Returns product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Sets product
     *
     * @param Product $product Product
     */
    public function setProduct(Product $product)
    {
        $this->product   = $product;
        $this->productId = $product->getId();
    }

    /**
     * Returns product id
     *
     * @return integer Product id
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Sets product id
     *
     * @param integer product_id Product id
     */
    public function setProductId($product_id)
    {
        $this->productId = $product_id;
    }

    /**
     * Returns quantity
     *
     * @return decimal
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sets quantity
     *
     * @param decimal $quantity Quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Returns total
     *
     * @return decimal Total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Computes total
     */
    public function compute()
    {
        $this->total = $this->getProduct()->getPrice() * $this->quantity;
    }

    /**
     * @see Serializable
     */
    public function serialize()
    {
        return serialize(array(
            'cart'       => $this->cart,
            'product_id' => $this->productId,
            'quantity'   => $this->quantity,
        ));
    }

    /**
     * @see Serializable
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->cart      = $data['cart'];
        $this->productId = $data['product_id'];
        $this->quantity  = $data['quantity'];
    }
}
