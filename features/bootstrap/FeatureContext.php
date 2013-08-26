<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\Tools\SchemaTool;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Branch,
    Isics\Bundle\OpenMiamMiamBundle\Entity\BranchDate,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Category,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Producer,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    use Behat\MinkExtension\Context\MinkDictionary;
    use Behat\Symfony2Extension\Context\KernelDictionary;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * @BeforeScenario
     */
    public function cleanDatabase()
    {
        $entityManager = $this->getEntityManager();
        $metadata      = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool    = new SchemaTool($entityManager);

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    /**
     * @Given /^there are following categories:$/
     */
    public function thereAreFollowingCategories(TableNode $table)
    {
        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $category = new Category();
            $category->setName($data['name']);

            $entityManager->persist($category);
        }

        $entityManager->flush();
    }

    /**
     * @Given /^there are following producers:$/
     */
    public function thereAreFollowingProducers(TableNode $table)
    {
        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $producer = new Producer();
            $producer->setName($data['name']);

            $entityManager->persist($producer);
        }

        $entityManager->flush();
    }

    /**
     * @Given /^an association "([^"]*)"$/
     */
    public function anAssociation($name)
    {
        $entityManager = $this->getEntityManager();

        $association = new Association();
        $association->setName($name);
        $association->setClosingDelay(86400);
        $association->setOpeningDelay(86400);
        $association->setDefaultCommission(10);

        $entityManager->persist($association);
        $entityManager->flush();
    }

    /**
     * @Given /^association "([^"]*)" has following branches:$/
     */
    public function associationHasFollowingBranches($association_name, TableNode $table)
    {
        $association = $this->getRepository('Association')->findOneByName($association_name);
        if (null === $association) {
            throw new \InvalidArgumentException(
                sprintf('Association named "%s" was not found.', $association_name)
            );
        }

        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $branch = new Branch();
            $branch->setAssociation($association);
            $branch->setName($data['name']);

            $entityManager->persist($branch);
        }

        $entityManager->flush();
    }

    /**
     * @Given /^association "([^"]*)" has following producers:$/
     */
    public function associationHasFollowingProducers($association_name, TableNode $table)
    {
        $association = $this->getRepository('Association')->findOneByName($association_name);
        if (null === $association) {
            throw new \InvalidArgumentException(
                sprintf('Association named "%s" was not found.', $association_name)
            );
        }

        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $producer = $this->getRepository('Producer')->findOneByName($data['name']);
            if (null === $producer) {
                throw new \InvalidArgumentException(
                    sprintf('Producer named "%s" was not found.', $data['name'])
                );
            }

            $association->addProducer($producer);
        }

        $entityManager->persist($association);
        $entityManager->flush();
    }

    /**
     * @Given /^producer "([^"]*)" has following products:$/
     */
    public function producerHasFollowingProducts($producer_name, TableNode $table)
    {
        $producer = $this->getRepository('Producer')->findOneByName($producer_name);
        if (null === $producer) {
            throw new \InvalidArgumentException(
                sprintf('Producer named "%s" was not found.', $producer_name)
            );
        }

        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $category = $this->getRepository('Category')->findOneByName($data['category']);
            if (null === $category) {
                throw new \InvalidArgumentException(
                    sprintf('Category named "%s" was not found.', $data['category'])
                );
            }

            $product = new Product();
            $product->setProducer($producer);
            $product->setName($data['name']);
            $product->setCategory($category);

            if (array_key_exists('description', $data)) {
                $product->setDescription($data['description']);
            }

            if (array_key_exists('price', $data) && '' !== $data['price']) {
                $product->setPrice($data['price']);
            }

            if (array_key_exists('availability', $data) && '' !== $data['availability']) {
                if ('available' === $data['availability']) {
                    $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
                } else if ('unavailable' === $data['availability']) {
                    $product->setAvailability(Product::AVAILABILITY_UNAVAILABLE);
                } else if ('available at' === substr($data['availability'], 0, 12)) {
                    $product->setAvailability(Product::AVAILABILITY_AVAILABLE_AT);
                    $product->setAvailableAt(new \DateTime(substr($data['availability'], 13)));
                } else if (false !== $pos = strpos($data['availability'], 'in stock')) {
                    $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
                    $product->setStock(substr($data['availability'], 0, $pos-1));
                } else {
                    throw new \InvalidArgumentException(
                        sprintf('"%s" is not a valid availability.', $data['availability'])
                    );
                }
            } else {
                $product->setAvailability(Product::AVAILABILITY_AVAILABLE);
            }

            $entityManager->persist($product);
        }

        $entityManager->flush();
    }

    /**
     * @Given /^branch "([^"]*)" has following calendar:$/
     */
    public function branchHasFollowingCalendar($branch_name, TableNode $table)
    {
        $branch = $this->getRepository('Branch')->findOneByName($branch_name);
        if (null === $branch) {
            throw new \InvalidArgumentException(
                sprintf('Branch named "%s" was not found.', $branch_name)
            );
        }

        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $branchDate = new BranchDate();
            $branchDate->setBranch($branch);
            $branchDate->setDate(new \DateTime($data['date']));
            $branchDate->setStartTime(new \DateTime($data['from']));
            $branchDate->setEndTime(new \DateTime($data['to']));

            $entityManager->persist($branchDate);
        }

        $entityManager->flush();
    }

    /**
     * @Given /^branch "([^"]*)" has following producers:$/
     */
    public function branchHasFollowingProducers($branch_name, TableNode $table)
    {
        $branch = $this->getRepository('Branch')->findOneByName($branch_name);
        if (null === $branch) {
            throw new \InvalidArgumentException(
                sprintf('Branch named "%s" was not found.', $branch_name)
            );
        }

        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $producer = $this->getRepository('Producer')->findOneByName($data['name']);
            if (null === $producer) {
                throw new \InvalidArgumentException(
                    sprintf('Producer named "%s" was not found.', $association_name)
                );
            }
            $branch->addProducer($producer);
        }

        $entityManager->persist($branch);
        $entityManager->flush();
    }

    /**
     * @Given /^branch "([^"]*)" has following products:$/
     */
    public function branchHasFollowingProducts($branch_name, TableNode $table)
    {
        $branch = $this->getRepository('Branch')->findOneByName($branch_name);
        if (null === $branch) {
            throw new \InvalidArgumentException(
                sprintf('Branch named "%s" of assocation named "%s" was not found.', $branch_name, $association_name)
            );
        }

        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $producer = $this->getRepository('Producer')->findOneByName($data['producer']);
            if (null === $producer) {
                throw new \InvalidArgumentException(
                    sprintf('Producer named "%s" was not found.', $data['producer'])
                );
            }

            $product = $this->getRepository('Product')->findOneBy(array(
                'name'     => $data['product'],
                'producer' => $producer
            ));
            if (null === $product) {
                throw new \InvalidArgumentException(
                    sprintf('Product named "%s" of producer named "%s" was not found.', $data['product'], $data['producer'])
                );
            }

            $branch->addProduct($product);
        }

        $entityManager->persist($branch);
        $entityManager->flush();
    }

    /**
     * @Given /^I change quantity to "([^"]*)"$/
     */
    public function iChangeQuantityTo($quantity)
    {
        $this->fillField('open_miam_miam_cart_items_1_quantity', $quantity);
    }

    /**
     * @Given /^I should see the next date "([^"]*)" formated "([^"]*)"$/
     */
    public function iShouldSeeTheNextDateFormated($time, $format)
    {
        $this->assertPageContainsText(date($format, strtotime($time)));
    }

    /**
     * Get entity manager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Get repository by entity Classname.
     *
     * @param string $entity_classname
     *
     * @return ObjectRepository
     */
    public function getRepository($entity_classname)
    {
        return $this->getContainer()->get('doctrine')->getRepository('IsicsOpenMiamMiamBundle:'.$entity_classname);
    }
}
