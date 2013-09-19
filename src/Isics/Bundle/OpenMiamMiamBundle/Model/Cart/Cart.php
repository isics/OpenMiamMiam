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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;

/**
 * A "light" order object (stored in session)
 */
class Cart implements \Serializable
{
    /**
     * @var boolean $closed
     */
    protected $closed;

    /**
     * @var Branch $branch
     */
    protected $branch;

    /**
     * @var array $items
     */
    protected $items;

    /**
     * @var decimal $total
     */
    protected $total;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->closed = false;
        $this->items  = array();
        $this->total  = 0;
    }

    /**
     * Clones items
     */
    public function __clone()
    {
        foreach ($this->items as $item) {
            $this->items[$item->getProductId()] = clone $this->items[$item->getProductId()];
        }
    }

    /**
     * Sets branch
     *
     * @param  Branch $branch
     */
    public function setBranch(Branch $branch)
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * Gets branch
     *
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Returns items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Returns number of items
     *
     * @return integer
     */
    public function getNbItems()
    {
        return count($this->items);
    }

    /**
     * Creates an item
     *
     * @return CartItem
     */
    public function createItem()
    {
        $item = new CartItem();
        $item->setCart($this);

        return $item;
    }

    /**
     * Adds an item
     *
     * @param CartItem $item    Cart item
     * @param boolean  $compute Compute or not
     */
    public function addItem(CartItem $item, $compute = true)
    {
        if (array_key_exists($item->getProductId(), $this->items)) {
            $this->items[$item->getProductId()]->setQuantity(
                $this->items[$item->getProductId()]->getQuantity() + $item->getQuantity()
            );
        } else {
            $this->items[$item->getProductId()] = $item;
        }

        if (0 === $this->items[$item->getProductId()]->getQuantity()) {
            $this->removeItems($item);
        }

        if ($compute) {
            $this->compute();
        }
    }

    /**
     * Removes an item
     *
     * @param CartItem $item    Cart item
     * @param boolean  $compute Compute or not
     */
    public function removeItem(CartItem $item, $compute = true)
    {
        unset($this->items[$item->getProductId()]);

        if ($compute) {
            $this->compute();
        }
    }

    /**
     * Sets items
     *
     * @param array $items Array of CartItem
     */
    public function setItems($items)
    {
        $this->clearItems(false);

        foreach ($items as $item) {
            $this->addItem($item, false);
        }

        $this->compute();
    }

    /**
     * Clears items
     *
     * @param boolean $compute Compute or not
     */
    public function clearItems($compute = true)
    {
        $this->items = array();

        if ($compute) {
            $this->compute();
        }
    }

    /**
     * Returns total
     *
     * @return decimal
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
        $this->total = 0;

        foreach ($this->items as $item) {
            $item->compute();
            $this->total += $item->getTotal();
        }
    }

    /**
     * Returns true if orders are closed
     *
     * @return boolean
     */
    public function isClosed()
    {
        return true === $this->closed;
    }

    /**
     * Closes orders
     */
    public function close()
    {
        $this->closed = true;
    }

    /**
     * @see Serializable
     */
    public function serialize()
    {
        return serialize(array(
            'items' => $this->items,
        ));
    }

    /**
     * @see Serializable
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->items = $data['items'];
    }


}
