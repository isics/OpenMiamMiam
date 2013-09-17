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
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;

class LoadBranchOccurrenceData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach (array('last week', '+1 week', '+ 2 weeks', '+ 3 weeks') as $time) {
            $branchOccurrence = new BranchOccurrence();
            $branchOccurrence->setBranch($this->getReference('Branch 1'));
            $branchOccurrence->setBegin(new \DateTime($time.' 5 p.m.'));
            $branchOccurrence->setEnd(new \DateTime($time.' 7 p.m.'));

            $manager->persist($branchOccurrence);

            $this->addReference('branch occurrence Branch 1 '.$time, $branchOccurrence);
        }

        foreach (array('last week', '+ 1 week', '+ 3 weeks') as $time) {
            $branchOccurrence = new BranchOccurrence();
            $branchOccurrence->setBranch($this->getReference('Branch 2'));
            $branchOccurrence->setBegin(new \DateTime($time.' 5 p.m.'));
            $branchOccurrence->setEnd(new \DateTime($time.' 7 p.m.'));

            $manager->persist($branchOccurrence);

            $this->addReference('branch occurrence Branch 2 '.$time, $branchOccurrence);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
