<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Tests\Twig;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Twig\CategoryLevelExtension;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class CategoryLevelExtensionTest extends WebTestCase
{
    public function setUp() {
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

    public function testGetRootCategory() {
        // Obtention de la catégorie "Porc"
        $category = $this->getParticularCategoryByName('Porc');

        // Demande de la catégorie de niveau 0 (root)
        $extension = new CategoryLevelExtension();
        $rootCategory = $extension->rootCategoryToLevel($category, 0);
        $this->assertEquals($rootCategory->getName(), 'Racine (invisible)');
    }

    public function testGetLevelOneCategory() {
        // Obtention de la catégorie "Porc"
        $category = $this->getParticularCategoryByName('Porc');

        // Demande de la catégorie tout juste supérieure
        $extension = new CategoryLevelExtension();
        $motherCategory = $extension->rootCategoryToLevel($category);
        $this->assertEquals($motherCategory->getName(), 'Viande');
    }

    public function testGetSameCategory() {
        // Obtention de la catégorie "Porc"
        $category = $this->getParticularCategoryByName('Porc');

        // Demande de la même catégorie
        $extension = new CategoryLevelExtension();
        $sameCategory = $extension->rootCategoryToLevel($category, 2);
        $this->assertEquals($sameCategory->getName(), 'Porc');
    }

    /**
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