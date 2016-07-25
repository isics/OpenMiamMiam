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
use Isics\Bundle\OpenMiamMiamBundle\Entity\ProducerAttendance;

class LoadProducerAttendanceData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $producerAttendance = new ProducerAttendance();
        $producerAttendance->setProducer($this->getReference('producer.beth_rave'));
        $producerAttendance->setBranchOccurrence($this->getReference('branch_occurrence.branch1_+1 week'));
        $producerAttendance->setIsAttendee(true);
        $manager->persist($producerAttendance);

        $producerAttendance = new ProducerAttendance();
        $producerAttendance->setProducer($this->getReference('producer.elsa_dorsa'));
        $producerAttendance->setBranchOccurrence($this->getReference('branch_occurrence.branch1_+1 week'));
        $producerAttendance->setIsAttendee(true);
        $manager->persist($producerAttendance);

        $producerAttendance = new ProducerAttendance();
        $producerAttendance->setProducer($this->getReference('producer.romeo_frigo'));
        $producerAttendance->setBranchOccurrence($this->getReference('branch_occurrence.branch1_+1 week'));
        $producerAttendance->setIsAttendee(true);
        $manager->persist($producerAttendance);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 7;
    }
}
