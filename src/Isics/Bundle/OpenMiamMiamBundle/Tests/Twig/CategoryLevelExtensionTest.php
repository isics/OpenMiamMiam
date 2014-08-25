<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Tests\Twig;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Twig\CategoryLevelExtension;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * Class CategoryLevelExtensionTest
 * Test of the Twig's extension CategoryLevelExtension
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Tests\Twig
 */
class CategoryLevelExtensionTest extends WebTestCase
{
    /**
     * Method called before each test
     */
    public function setUp() {
        // Loading fixtures
        $classes = array(
            'Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM\LoadUserData',
            'Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM\LoadAssociationData',
            'Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM\LoadBranchData',
            'Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM\LoadBranchOccurrenceData',
            'Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM\LoadCategoryData',
            'Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM\LoadProducerAttendanceData',
            'Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM\LoadProducerData',
            'Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM\LoadProductData'
        );
        $this->loadFixtures($classes);
    }

    /**
     * Test of getting the root category
     */
    public function testGetRootCategory() {
        // Getting the "Porc" category
        $category = $this->getParticularCategoryByName('Porc');

        // Ask for the level 0 category (root)
        $extension = new CategoryLevelExtension();
        $rootCategory = $extension->rootCategoryToLevel($category, 0);
        $this->assertEquals($rootCategory->getName(), 'Racine (invisible)');
    }

    /**
     * Test of getting the level 1 category
     */
    public function testGetLevelOneCategory() {
        // Getting the "Porc" category
        $category = $this->getParticularCategoryByName('Porc');

        // Ask for the parent category
        $extension = new CategoryLevelExtension();
        $motherCategory = $extension->rootCategoryToLevel($category);
        $this->assertEquals($motherCategory->getName(), 'Viande');
    }

    /**
     * Test of the direct return of the category
     */
    public function testGetSameCategory() {
        // Getting the "Porc" category
        $category = $this->getParticularCategoryByName('Porc');

        // Ask for the same category
        $extension = new CategoryLevelExtension();
        $sameCategory = $extension->rootCategoryToLevel($category, 2);
        $this->assertEquals($sameCategory->getName(), 'Porc');
    }

    /**
     * Just return the object Category by the given name
     *
     * @param $name
     * @return Category
     */
    protected function getParticularCategoryByName($name) {
        return
            $this
                ->getContainer()
                ->get('doctrine')
                ->getManager()
                ->getRepository('IsicsOpenMiamMiamBundle:Category')
                ->findOneBy(
                    array(
                        'name' => $name
                    )
                )
            ;
    }
}