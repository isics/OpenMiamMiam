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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
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

        foreach (array('branch.branch1', 'branch.branch2') as $name) {
            $branch = new Branch();
            $branch->setName($this->translator->trans($name, array(), 'fixtures'));
            $branch->setAssociation($this->getReference('association'));
            $branch->setPresentation($faker->text);
            $branch->setAddress1($faker->streetAddress);
            $branch->setZipcode($faker->postcode);
            $branch->setCity($faker->city);

            $manager->persist($branch);

            $this->addReference($name, $branch);
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
