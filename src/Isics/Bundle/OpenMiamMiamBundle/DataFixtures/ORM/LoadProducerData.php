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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class LoadProducerData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach (array('Beth Rave', 'Elsa Dorsa', 'Romeo Frigo') as $name) {
            $producer = new Producer();
            $producer->setName($name);
            $manager->persist($producer);

            $this->getReference('association')->addProducer($producer);
            $this->getReference('Branch 1')->addProducer($producer);
            $this->getReference('Branch 2')->addProducer($producer);

            $this->addReference($name, $producer);
        }

        $manager->persist($this->getReference('association'));
        $manager->persist($this->getReference('Branch 1'));
        $manager->persist($this->getReference('Branch 2'));

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5;
    }
}
