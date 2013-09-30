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

class BranchOccurrenceManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $dates;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager Object Manager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->dates         = array();
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
     * Returns infos about product availability
     *
     * @param Branch  $branch
     * @param Product $product
     *
     * @return ProductAvailability
     */
    public function getProductAvailability(Branch $branch, Product $product)
    {
        $productAvailability = new ProductAvailability();

        if (!$this->hasNext($branch)) {
            $productAvailability->setReason(ProductAvailability::REASON_NO_NEXT_BRANCH_OCCURRENCE);
        } else if (true !== $this->getNext($branch)->isProducerAttendee($product->getProducer())) {
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
                    if ($this->getNext($branch)->getBegin() >= $product->getAvailableAt()) {
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
            $nextForBranches[] = $repository->findOneNextForBranch($branch);
        }

        $occurrences = array_merge($latest, $nextForBranches);
        usort($occurrences, array($this, 'sortOccurrences'));

        return $occurrences;
    }
}
