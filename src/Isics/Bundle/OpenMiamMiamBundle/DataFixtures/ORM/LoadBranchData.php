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

class LoadBranchData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create();
        $faker->addProvider(new Faker\Provider\fr_FR\Address($faker));

        foreach (array('Lorem', 'Ipsum') as $name) {
            $branch = new Branch();
            $branch->setName($name);
            $branch->setAssociation($this->getReference('association'));
            $branch->setPresentation($faker->text);
            $branch->setAddress1($faker->streetAddress);
            $branch->setZipcode($faker->postcode);
            $branch->setCity($faker->city);

            $manager->persist($branch);

            $this->addReference('branch '.$name, $branch);
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