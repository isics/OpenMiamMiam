<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Behat;

use Behat\Behat\Context\BehatContext,
    Behat\Gherkin\Node\TableNode;

use Doctrine\ORM\Tools\SchemaTool;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Branch,
    Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Category,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Producer,
    Isics\Bundle\OpenMiamMiamBundle\Entity\ProducerAttendance,
    Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity,
    Symfony\Component\Security\Acl\Domain\UserSecurityIdentity,
    Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    use \Behat\MinkExtension\Context\MinkDictionary;
    use \Behat\Symfony2Extension\Context\KernelDictionary;

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
            $entityManager->flush();

            # ACL
            $aclProvider = $this->getContainer()->get('security.acl.provider');

            $objectIdentity = ObjectIdentity::fromDomainObject($producer);
            $acl = $aclProvider->findAcl($objectIdentity);

            if (!empty($data['managers'])) {
                $managers = explode(',', $data['managers']);
                foreach ($managers as $manager) {
                    $securityIdentity = new UserSecurityIdentity(trim($manager), 'Isics\Bundle\OpenMiamMiamUserBundle\Entity\User');
                    $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
                    $aclProvider->updateAcl($acl);
                }
            }
        }
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

        $productManager = $this->getContainer()->get('open_miam_miam.product_manager');

        foreach ($table->getHash() as $data) {
            $category = $this->getRepository('Category')->findOneByName($data['category']);
            if (null === $category) {
                throw new \InvalidArgumentException(
                    sprintf('Category named "%s" was not found.', $data['category'])
                );
            }

            $product = $productManager->createForProducer($producer);
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

            $productManager->save($product);
        }
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
            $branchOccurrence = new BranchOccurrence();
            $branchOccurrence->setBranch($branch);
            $branchOccurrence->setBegin(new \DateTime($data['date'].' '.$data['from']));
            $branchOccurrence->setEnd(new \DateTime($data['date'].' '.$data['to']));

            $entityManager->persist($branchOccurrence);
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
                sprintf('Branch named "%s" was not found.', $branch_name)
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
     * @Given /^producer "([^"]*)" will be present to following occurrences:$/
     */
    public function producerWillBePresentToFollowingOccurrences($producer_name, TableNode $table)
    {
        $producer = $this->getRepository('Producer')->findOneByName($producer_name);
        if (null === $producer) {
            throw new \InvalidArgumentException(
                sprintf('Producer named "%s" was not found.', $producer_name)
            );
        }

        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $branch = $this->getRepository('Branch')->findOneByName($data['branch']);
            if (null === $branch) {
                throw new \InvalidArgumentException(
                    sprintf('Branch named "%s" was not found.', $data['branch'])
                );
            }

            $branchOccurrence = $this->getRepository('BranchOccurrence')
                ->createQueryBuilder('bo')
                ->where('bo.branch = :branch')
                ->andWhere('bo.begin >= :date1')
                ->andWhere('bo.begin < :date2')
                ->setParameter('branch', $branch)
                ->setParameter('date1', new \DateTime($data['date'].' 12 a.m.'))
                ->setParameter('date2', new \DateTime($data['date'].' 12 a.m. + 1 day'))
                ->getQuery()
                ->getOneOrNullResult();
            if (null === $branchOccurrence) {
                throw new \InvalidArgumentException(
                    sprintf('No branch occurrence for branch named "%s" at %s.', $data['branch'], date('c', strtotime($data['date'])))
                );
            }

            $producerAttendance = new ProducerAttendance();
            $producerAttendance->setProducer($producer);
            $producerAttendance->setBranchOccurrence($branchOccurrence);
            $producerAttendance->setIsAttendee(true);

            $entityManager->persist($producerAttendance);
        }

        $entityManager->flush();
    }

    /**
     * @Given /^Product "([^"]*)" of producer "([^"]*)" has stock level "([^"]*)"$/
     */
    public function productOfProducerHasStockLevel($product_name, $producer_name, $stock)
    {
        $producer = $this->getRepository('Producer')->findOneByName($producer_name);
        if (null === $producer) {
            throw new \InvalidArgumentException(
                sprintf('Producer named "%s" was not found.', $producer_name)
            );
        }

        $product = $this->getRepository('Product')->findOneBy(array(
            'name'     => $product_name,
            'producer' => $producer
        ));
        if (null === $product) {
            throw new \InvalidArgumentException(
                sprintf('Product named "%s" of producer named "%s" was not found.', $product_name, $producer_name)
            );
        }

        $entityManager = $this->getEntityManager();

        $product->setAvailability(Product::AVAILABILITY_ACCORDING_TO_STOCK);
        $product->setStock($stock);

        $entityManager->persist($product);
        $entityManager->flush();
    }

    /**
     * @Given /^Product "([^"]*)" of producer "([^"]*)" will be available at "([^"]*)"$/
     */
    public function productOfProducerWillBeAvailableAt($product_name, $producer_name, $date)
    {
        $producer = $this->getRepository('Producer')->findOneByName($producer_name);
        if (null === $producer) {
            throw new \InvalidArgumentException(
                sprintf('Producer named "%s" was not found.', $producer_name)
            );
        }

        $product = $this->getRepository('Product')->findOneBy(array(
            'name'     => $product_name,
            'producer' => $producer
        ));
        if (null === $product) {
            throw new \InvalidArgumentException(
                sprintf('Product named "%s" of producer named "%s" was not found.', $product_name, $producer_name)
            );
        }

        $entityManager = $this->getEntityManager();

        $product->setAvailability(Product::AVAILABILITY_AVAILABLE_AT);
        $product->setAvailableAt(new \DateTime($date));

        $entityManager->persist($product);
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
     * @When /^I fill username field with "([^"]*)"$/
     */
    public function iFillUsernameFieldWith($username)
    {
        $this->fillField('username', $username);
    }

    /**
     * @Given /^I fill password field with "([^"]*)"$/
     */
    public function iFillPasswordFieldWith($password)
    {
        $this->fillField('password', $password);
    }

    /**
     * @Given /^I press login button$/
     */
    public function iPressLoginButton()
    {
        $this->pressButton('_submit');
    }

    /**
     * @Given /^the administration area switcher should be on "([^"]*)"$/
     */
    public function theAdministrationAreaSwitcherShouldBeOn($value)
    {
        $this->assertElementContains('#admin-switcher', 'selected="selected">'.$value);
    }

    /**
     * @Given /^I should see the next date "([^"]*)" formated "([^"]*)"$/
     */
    public function iShouldSeeTheNextDateFormated($time, $format)
    {
        $this->assertPageContainsText(date($format, strtotime($time)));
    }

    /**
     * @Given /^there are following users:$/
     */
    public function thereAreFollowingUsers(TableNode $table)
    {
        $userManager = $this->getContainer()->get('fos_user.user_manager');

        foreach ($table->getHash() as $data) {
            $user = $userManager->createUser();
            $user->setUsername($data['email']);
            $user->setPlainPassword($data['password']);
            $user->setEmail($data['email']);
            $user->setEnabled(true);
            $user->setSuperAdmin(false);
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $user->setAddress1($data['address_1']);
            $user->setAddress2($data['address_2']);
            $user->setZipCode($data['zip_code']);
            $user->setCity($data['city']);
            $userManager->updateUser($user);
        }
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
