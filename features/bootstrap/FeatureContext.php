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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Branch,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Category;

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
     * @Given /^an association "([^"]*)"$/
     */
    public function anAssociation($name)
    {
        $entityManager = $this->getEntityManager();

        $association = new Association();
        $association->setName($name);

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
    public function associationHasFollowingProducers($arg1, TableNode $table)
    {
        throw new PendingException();
    }

    /**
     * @Given /^producer "([^"]*)" has following products:$/
     */
    public function producerHasFollowingProducts($arg1, TableNode $table)
    {
        throw new PendingException();
    }

    /**
     * @Given /^I am on the branch "([^"]*)" homepage$/
     */
    public function iAmOnTheBranchHomepage($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^I should see "([^"]*)" and "([^"]*)"$/
     */
    public function iShouldSeeAnd($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Given /^I should not see "([^"]*)"$/
     */
    public function iShouldNotSee($arg1)
    {
        throw new PendingException();
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
