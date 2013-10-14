<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

/**
 * Class BranchManager
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class BranchManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var ActivityManager $activityManager
     */
    protected $activityManager;


    /**
     * Constructs object
     *
     * @param EntityManager   $entityManager
     * @param ActivityManager $activityManager
     */
    public function __construct(EntityManager $entityManager, ActivityManager $activityManager)
    {
        $this->entityManager   = $entityManager;
        $this->activityManager = $activityManager;
    }

    /**
     * Returns a new branch for a association
     *
     * @param Association $association
     *
     * @return Branch
     */
    public function createForAssociation(Association $association)
    {
        $branch = new Branch();
        $branch->setAssociation($association);

        return $branch;
    }

    /**
     * Saves a branch
     *
     * @param Branch $branch
     * @param User   $user
     */
    public function save(Branch $branch, User $user = null)
    {
        $association = $branch->getAssociation();

        $activityTransKey = null;
        if (null === $branch->getId()) {
            $activityTransKey = 'activity_stream.branch.created';
        } else {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($branch);
            if (!empty($changeSet)) {
                $activityTransKey = 'activity_stream.branch.updated';
            }
        }

        // Save object
        $this->entityManager->persist($branch);
        $this->entityManager->flush();

        // Activity
        if (null !== $activityTransKey) {
            $activity = $this->activityManager->createFromEntities(
                $activityTransKey,
                array('%name%' => $branch->getName()),
                $branch,
                $association,
                $user
            );
            $this->entityManager->persist($activity);
            $this->entityManager->flush();
        }
    }

    /**
     * Deletes a branch
     *
     * @param Branch $branch
     */
    public function delete(Branch $branch)
    {
        $this->entityManager->remove($branch);
        $this->entityManager->flush();
    }

    /**
     * Returns activities of a branch
     *
     * @param Branch $branch
     *
     * @return array
     */
    public function getActivities(Branch $branch)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Activity')->findByEntities($branch, $branch->getAssociation());
    }
}
