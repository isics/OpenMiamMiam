<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Tests\Twig;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Twig\CategoryLevelExtension;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Class CategoryLevelExtensionTest
 * Test of the Twig's extension CategoryLevelExtension
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Tests\Twig
 */
class CategoryLevelExtensionTest extends WebTestCase
{
    /**
     * @var CategoryLevelExtension
     */
    protected $extension = null;

    /**
     * Method called before each test
     */
    public function setUp()
    {
        // Truncate category table
        // In order to select a category by her id
        $this->truncateTable('Isics\Bundle\OpenMiamMiamBundle\Entity\Category');

        // Loading fixtures
        self::runCommand('doctrine:fixtures:load', array('-n' => true, '--fixtures' => __DIR__ . '/../../DataFixtures/ORM/'));

        // Prepare the extension to test
        /** @var Translator $translator */
        $translator = $this->getContainer()->get('translator');
        $this->extension = new CategoryLevelExtension($translator);
    }

    /**
     * Method to empty a table & reset auto increments
     *
     * @param $classname
     */
    protected function truncateTable($classname)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $cmd = $em->getClassMetadata($classname);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS = 0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Test with an impossible level
     *
     * @expectedException \Isics\Bundle\OpenMiamMiamBundle\Exception\BadLevelException
     */
    public function testWrongLevel()
    {
        $this->extension->findParentCategoryAtLevel(new Category(), -3);
    }

    /**
     * Test of getting the root category
     */
    public function testGetRootCategory()
    {
        // Getting the "Porc" category
        $category = $this->getParticularCategory(7);

        // Ask for the level 0 category (root)
        $rootCategory = $this->extension->findParentCategoryAtLevel($category, 0);
        $this->assertEquals($rootCategory->getName(), 'Racine (invisible)');
    }

    /**
     * Test of getting the level 1 category
     */
    public function testGetLevelOneCategory()
    {
        // Getting the "Porc" category
        $category = $this->getParticularCategory(7);

        // Ask for the parent category
        $motherCategory = $this->extension->findParentCategoryAtLevel($category);
        $this->assertEquals($motherCategory->getName(), 'Viande');
    }

    /**
     * Test of the direct return of the category
     */
    public function testGetSameCategory()
    {
        // Getting the "Porc" category
        $category = $this->getParticularCategory(7);

        // Ask for the same category
        $sameCategory = $this->extension->findParentCategoryAtLevel($category, 2);
        $this->assertEquals($sameCategory->getName(), 'Porc');
    }

    /**
     * Just return the category by the given id
     *
     * @param $id
     * @return Category
     */
    protected function getParticularCategory($id)
    {
        return
            $this
                ->getContainer()
                ->get('doctrine')
                ->getManager()
                ->getRepository('IsicsOpenMiamMiamBundle:Category')
                ->find($id);
    }
}