<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Category;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;

class CategoryNode
{
    const POSITION_FIRST_CHILD     = 0;
    const POSITION_FIRST_CHILD_OF  = 1;
    const POSITION_LAST_CHILD_OF   = 2;
    const POSITION_PREV_SIBLING_OF = 3;
    const POSITION_NEXT_SIBLING_OF = 4;

    /**
     * @var Category $category
     */
    protected $category;

    /**
     * @var integer $position
     */
    protected $position;

    /**
     * @var Category $target
     */
    protected $target;


    /**
     * Constructor
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set category name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->category->setName($name);
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getName()
    {
        return $this->category->getName();
    }

    /**
     * Set position
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set target
     *
     * @param Category $target
     */
    public function setTarget(Category $target)
    {
        $this->target = $target;
    }

    /**
     * Get target
     *
     * @return Category
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Return true if node is root
     *
     * @return boolean
     */
    public function isRoot()
    {
        return self::POSITION_FIRST_CHILD === $this->position;
    }
}