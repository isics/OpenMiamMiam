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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;

class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $fruitsAndVegetables = new Category();
        $fruitsAndVegetables->setName('Fruits and vegetables');
        $this->addReference($fruitsAndVegetables->getName(), $fruitsAndVegetables);

        $dairyProduce = new Category();
        $dairyProduce->setName('Dairy produce');
        $this->addReference($dairyProduce->getName(), $dairyProduce);

        $meat = new Category();
        $meat->setName('Meat');
        $this->addReference($meat->getName(), $meat);

        $beef = new Category();
        $beef->setName('Beef');
        $beef->setParent($meat);
        $this->addReference($beef->getName(), $beef);

        $lamb = new Category();
        $lamb->setName('Lamb');
        $lamb->setParent($meat);
        $this->addReference($lamb->getName(), $lamb);

        $pork = new Category();
        $pork->setName('Pork');
        $pork->setParent($meat);
        $this->addReference($pork->getName(), $pork);

        $manager->persist($fruitsAndVegetables);
        $manager->persist($dairyProduce);
        $manager->persist($meat);
        $manager->persist($beef);
        $manager->persist($lamb);
        $manager->persist($pork);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
