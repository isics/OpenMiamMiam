<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Twig;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\CategoryRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Exception\BadLevelException;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Twig_Extension;

/**
 * Class CategoryLevelExtension
 * Allows you to get the parent (or grandparent or more) switch the asked level of a category
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Twig
 */
class CategoryLevelExtension extends Twig_Extension
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var \Entity\Repository\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator, CategoryRepository $categoryRepository)
    {
        $this->translator = $translator;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Name of the extension
     *
     * @return string
     */
    public function getName()
    {
        return 'category_at_level';
    }

    /**
     * Available filters
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('category_at_level', array($this, 'findParentCategoryAtLevel'))
        );
    }

    /**
     * Method to get the parent ( switch the asked level ) of a category
     *
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\Category $category
     * @param int $level
     * @throws \Isics\Bundle\OpenMiamMiamBundle\Exception\BadLevelException
     * @return Category
     */
    public function findParentCategoryAtLevel(Category $category, $level)
    {
        // Cast to int
        $level = (int)$level;
        // Check that the level is greater than or equals 0
        if ($level < 0) {
            throw new BadLevelException(
                $this->translator->trans('category.badlevel', array(), 'exceptions')
            );
        }

        // Get the current level ...
        $currentLevel = $category->getLvl();
        // ... and check if he's greater than the asked one
        if ($currentLevel <= $level) {
            return $category;
        }

        // Else find the asked parent
        return $this->categoryRepository->getCategoryAtLevel($category, $level);
    }
}