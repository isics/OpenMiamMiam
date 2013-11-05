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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Model\Product\ProductAvailability;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class BranchOccurrenceManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ActivityManager $activityManager
     */
    protected $activityManager;

    /**
     * @var array
     */
    protected $dates;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager Object Manager
     * @param ActivityManager $activityManager
     */
    public function __construct(EntityManager $entityManager, ActivityManager $activityManager)
    {
        $this->entityManager   = $entityManager;
        $this->activityManager = $activityManager;
        $this->dates           = array();
    }

    /**
     * Create an occurrence for a branch
     *
     * @param Branch $branch
     *
     * @return BranchOccurrence
     */
    public function createForBranch(Branch $branch)
    {
        $branchOccurrence = new BranchOccurrence();
        $branchOccurrence->setBranch($branch);

        // 2 farthest occurrences
        $farthestOccurrences = $this->entityManager
            ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
            ->findFarthestForBranch($branch, 2);

        $nbFarthestOccurrences = count($farthestOccurrences);

        // No farthest occurrence: default tomorrow 8 A.M. - 10 P.M.
        if (0 === $nbFarthestOccurrences) {
            $branchOccurrence->setBegin(new \DateTime('tomorrow 8 am'));
            $branchOccurrence->setEnd(new \DateTime('tomorrow 10 am'));
        // 1 farthest occurrence: default +1 week, sames hours
        } else if (1 === $nbFarthestOccurrences) {
            $begin = clone $farthestOccurrences[0]->getBegin();
            $end   = clone $farthestOccurrences[0]->getEnd();
            $branchOccurrence->setBegin($begin->modify('+1 week'));
            $branchOccurrence->setEnd($end->modify('+1 week'));
        // 2 farthest occurrences: same frequence, sames hours
        } else if (2 === $nbFarthestOccurrences) {
            $begin = clone $farthestOccurrences[0]->getBegin();
            $end   = clone $farthestOccurrences[0]->getEnd();
            $diff  = $farthestOccurrences[1]->getBegin()->diff($farthestOccurrences[0]->getBegin())->format('%d');
            $branchOccurrence->setBegin($begin->modify(sprintf('+%s days', $diff)));
            $branchOccurrence->setEnd($end->modify(sprintf('+%s days', $diff)));
        }

        return $branchOccurrence;
    }

    /**
     * Saves a branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     * @param User             $user
     */
    public function save(BranchOccurrence $branchOccurrence, User $user = null)
    {
        $association = $branchOccurrence->getBranch()->getAssociation();

        $activityTransKey = null;
        if (null === $branchOccurrence->getId()) {
            $activityTransKey = 'activity_stream.branch_occurrence.created';
        } else {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($branchOccurrence);
            if (!empty($changeSet)) {
                $activityTransKey = 'activity_stream.branch_occurrence.updated';
            }
        }

        // Save object
        $this->entityManager->persist($branchOccurrence);
        $this->entityManager->flush();

        // Activity
        if (null !== $activityTransKey) {
            $activity = $this->activityManager->createFromEntities(
                $activityTransKey,
                array(
                    '%name%' => $branchOccurrence->getBranch()->getName(),
                    '%date%' => $branchOccurrence->getBegin(),
                ),
                $branchOccurrence,
                $association,
                $user
            );
            $this->entityManager->persist($activity);
            $this->entityManager->flush();
        }
    }

    /**
     * Deletes a branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     */
    public function delete(BranchOccurrence $branchOccurrence)
    {
        $this->entityManager->remove($branchOccurrence);
        $this->entityManager->flush();
    }

    /**
     * Returns in progress branch occurrence (if exists)
     *
     * @param Branch $branch
     *
     * @return BranchOccurrence|null
     */
    public function getInProgress(Branch $branch)
    {
        if (!array_key_exists($branch->getId(), $this->dates)) {
            $this->dates[$branch->getId()] = array();
        }

        if (!array_key_exists('in_progress', $this->dates[$branch->getId()])) {
            $this->dates[$branch->getId()]['in_progress'] = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
                ->findOneInProgressForBranch($branch);
        }

        return $this->dates[$branch->getId()]['in_progress'];
    }

    /**
     * Returns next branch occurrence
     *
     * @param Branch $branch
     *
     * @return BranchOccurrence|null
     */
    public function getNext(Branch $branch)
    {
        if (!array_key_exists($branch->getId(), $this->dates)) {
            $this->dates[$branch->getId()] = array();
        }

        if (!array_key_exists('next', $this->dates[$branch->getId()])) {
            $this->dates[$branch->getId()]['next'] = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
                ->findOneNextForBranch($branch);
        }

        return $this->dates[$branch->getId()]['next'];
    }

    /**
     * Returns true if a next branch date exists
     *
     * @param Branch $branch
     *
     * @return boolean
     */
    public function hasNext(Branch $branch)
    {
        return null !== $this->getNext($branch);
    }

    /**
     * Returns next branch occurrence not closed for an association
     *
     * @param Association $association Association
     *
     * @return BranchOccurrence|null
     */
    public function getNextNotClosedForAssociation(Association $association) {
        foreach ($association->getBranches() as $branch) {
            $branchOccurrence = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
                    ->findOneNextNotClosedForBranch($branch);
            if (null !== $branchOccurrence) {
                return $branchOccurrence;
            }
        }

        return null;
    }

    /**
     * Returns true if a branch occurrence is in progress
     *
     * @param Branch $branch
     *
     * @return boolean
     */
    public function isInProgress(Branch $branch)
    {
        return null !== $this->getInProgress($branch);
    }

    /*
     * Returns closing date
     *
     * @param Branch $branch
     *
     * @return \DateTime|null
     */
    public function getClosingDateTime(Branch $branch)
    {
        if (!$this->getNext($branch)) {
            return null;
        }

        $closingDelay = new \DateInterval(sprintf(
            'PT%sS',
            $branch->getAssociation()->getClosingDelay()
        ));

        $begin = clone $this->getNext($branch)->getBegin();

        return $begin->sub($closingDelay);
    }

    /**
     * Returns next opening date
     *
     * @param Branch $branch
     *
     * @return \DateTime|null
     */
    public function getOpeningDateTime(Branch $branch)
    {
        if (!$this->isInProgress($branch)) {
            return null;
        }

        $openingDelay = new \DateInterval(sprintf(
            'PT%sS',
            $branch->getAssociation()->getOpeningDelay()
        ));

        $end = clone $this->getInProgress($branch)->getEnd();

        return $end->add($openingDelay);
    }

    /**
     * Returns infos about product availability for next occurrence of a branch
     *
     * @param Branch  $branch
     * @param Product $product
     *
     * @return ProductAvailability
     */
    public function getProductAvailabilityForNext(Branch $branch, Product $product)
    {
        if ($this->hasNext($branch)) {
            return $this->getProductAvailability($this->getNext($branch), $product);
        }

        $productAvailability = new ProductAvailability($product);

        return $productAvailability->setReason(ProductAvailability::REASON_NO_NEXT_BRANCH_OCCURRENCE);
    }

    /**
     * Returns infos about product availability for a branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     * @param Product $product
     *
     * @return ProductAvailability
     */
    public function getProductAvailability(BranchOccurrence $branchOccurrence, Product $product)
    {
        $productAvailability = new ProductAvailability($product);

        if (true !== $branchOccurrence->isProducerAttendee($product->getProducer())) {
            $productAvailability->setReason(ProductAvailability::REASON_PRODUCER_ABSENT);
        } else {
            switch ($product->getAvailability()) {
                case Product::AVAILABILITY_UNAVAILABLE:
                    $productAvailability->setReason(ProductAvailability::REASON_UNAVAILABLE);
                    break;

                case Product::AVAILABILITY_ACCORDING_TO_STOCK:
                    if ($product->getStock() > 0) {
                        $productAvailability->setReason(ProductAvailability::REASON_IN_STOCK);
                    } else {
                        $productAvailability->setReason(ProductAvailability::REASON_OUT_OF_STOCK);
                    }
                    break;

                case Product::AVAILABILITY_AVAILABLE_AT:
                    if ($branchOccurrence->getBegin() >= $product->getAvailableAt()) {
                        $productAvailability->setReason(ProductAvailability::REASON_AVAILABLE);
                    } else {
                        $productAvailability->setReason(ProductAvailability::REASON_AVAILABLE_AT);
                    }
                    break;

                case Product::AVAILABILITY_AVAILABLE:
                    $productAvailability->setReason(ProductAvailability::REASON_AVAILABLE);
            }
        }

        return $productAvailability;
    }

    /**
     * Sort occurrences by begin date and branch name
     *
     * @param BranchOccurrence $occurrence1
     * @param BranchOccurrence $occurrence2
     *
     * @return bool
     */
    public static function sortOccurrences(BranchOccurrence $occurrence1, BranchOccurrence $occurrence2)
    {
        if ($occurrence1->getBegin() == $occurrence2->getBegin()) {
            return $occurrence1->getBranch()->getName() > $occurrence2->getBranch()->getName();
        }

        return $occurrence1->getBegin() > $occurrence2->getBegin();
    }

    /**
     * Returns occurrences to process for an association
     *
     * @param Association $association
     *
     * @return array
     */
    public function getToProcessForAssociation(Association $association)
    {
        $repository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence');
        $latest = $repository->findAllLatestForAssociation($association);

        $nextForBranches = array();
        foreach ($association->getBranches() as $branch) {
            $nextBranchOccurrence = $repository->findOneNextNotClosedForBranch($branch);
            if (null !== $nextBranchOccurrence) {
                $nextForBranches[] = $nextBranchOccurrence;
            }
        }

        $occurrences = array_merge($latest, $nextForBranches);
        usort($occurrences, array($this, 'sortOccurrences'));

        return $occurrences;
    }
}
