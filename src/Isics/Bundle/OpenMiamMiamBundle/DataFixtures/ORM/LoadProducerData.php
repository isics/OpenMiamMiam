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
        foreach (array('producer.beth_rave' => 'Beth Rave', 'producer.elsa_dorsa' => 'Elsa Dorsa', 'producer.romeo_frigo' => 'Romeo Frigo') as $key => $name) {
            $producer = new Producer();
            $producer->setName($name);
            $manager->persist($producer);

            $this->getReference('association')->addProducer($producer);
            $this->getReference('branch.branch1')->addProducer($producer);
            $this->getReference('branch.branch2')->addProducer($producer);

            $this->addReference($key, $producer);
        }

        $manager->persist($this->getReference('association'));
        $manager->persist($this->getReference('branch.branch1'));
        $manager->persist($this->getReference('branch.branch2'));

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
