<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Admin;

use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResourceInterface;

/**
 * Class AdminResourceCollection
 */
class AdminResourceCollection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $elements;

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->add($value, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this);
    }

    /**
     * Adds admin resource to elements
     *
     * @param AdminResourceInterface $resource
     * @param mixed $offset
     */
    public function add(AdminResourceInterface $resource, $offset = null)
    {
        if (null === $offset) {
            $this->elements[] = $resource;
        } else {
            $this->elements[$offset] = $resource;
        }
    }

    /**
     * Returns elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Returns elements count
     *
     * @return int
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * Returns the first element
     *
     * @return AdminResourceInterface
     */
    public function getFirst()
    {
        return reset($this->elements);
    }
}
