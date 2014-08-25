<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Twig;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Twig_Extension;

/**
 * Class CategoryLevelExtension
 * Allows you to get the parent (or grandparent or more) switch the asked level
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Twig
 */
class CategoryLevelExtension extends Twig_Extension
{
    /**
     * Name of the extension
     *
     * @return string
     */
    public function getName()
    {
        return 'category_level';
    }

    /**
     * Available filters
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('level', array($this, 'rootCategoryToLevel')),
        );
    }

    /**
     * Method to get the parent of a category switch the asked level
     *
     * @param Category $category
     * @param int $level
     * @return Category
     */
    public function rootCategoryToLevel(Category $category, $level = 1) {
        // Get the current level ...
        $currentLevel = $category->getLvl();
        // ... and check if he's greater than the asked one
        if($currentLevel <= $level) {
            return $category;
        }

        // Else do the loop to find the asked parent
        do {
            /** @var Category $category */
            $category = $category->getParent();
            $currentLevel = $category->getLvl();
        } while($currentLevel > $level);

        return $category;
    }
}