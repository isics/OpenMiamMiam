<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LoadBranchData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
        $this->translator->setLocale($this->container->getParameter('locale'));
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create();
        $faker->addProvider(new Faker\Provider\fr_FR\Address($faker));

        for ($i = 1; $i <= 2; $i++) {
            $branch = new Branch();
            $branch->setAssociation($this->getReference('association'));
            $branch->setPresentation($faker->text);
            $branch->setAddress1($faker->streetAddress);
            $branch->setZipcode($faker->postcode);
            $branch->setCity($faker->city);
            $branch->setDepartmentNumber($faker->departmentNumber);

            $manager->persist($branch);

            $this->addReference('branch.branch'.$i, $branch);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
