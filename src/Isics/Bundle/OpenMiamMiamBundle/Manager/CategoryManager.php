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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Model\Category\CategoryNode;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

/**
 * Class CategoryManager
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class CategoryManager
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
     * Returns a new categoryNode
     *
     * @param Category $category
     *
     * @return CategoryNode
     */
    public function createNode(Category $category = null)
    {
        if (null === $category) {
            $category = new Category();
        }

        $categoryNode = new CategoryNode($category);

        if (null === $category->getId()) {
            $categoryNode->setPosition(CategoryNode::POSITION_LAST_CHILD);
        } else {
            if (null !== $prev = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:Category')
                ->getPrevSibling($category)) {
                $categoryNode->setPosition(CategoryNode::POSITION_NEXT_SIBLING_OF);
                $categoryNode->setTarget($prev);
            } else if (null !== $next = $this->entityManager
                ->getRepository('IsicsOpenMiamMiamBundle:Category')
                ->getNextSibling($category)) {
                $categoryNode->setPosition(CategoryNode::POSITION_PREV_SIBLING_OF);
                $categoryNode->setTarget($next);
            } else {
                $categoryNode->setPosition(CategoryNode::POSITION_LAST_CHILD);
            }
        }

        return $categoryNode;
    }

    /**
     * Saves a category
     *
     * @param CategoryNode $categoryNode
     * @param User         $user
     */
    public function saveNode(CategoryNode $categoryNode, User $user = null)
    {
        $category = $categoryNode->getCategory();

        // Save object
        $repository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Category');
        switch ($categoryNode->getPosition()) {
            case CategoryNode::POSITION_FIRST_CHILD:
                $repository->persistAsFirstChild($category);
                break;

            case CategoryNode::POSITION_FIRST_CHILD_OF:
                $repository->persistAsFirstChildOf($category, $categoryNode->getTarget());
                break;

            case CategoryNode::POSITION_LAST_CHILD_OF:
                $repository->persistAsLastChildOf($category, $categoryNode->getTarget());
                break;

            case CategoryNode::POSITION_PREV_SIBLING_OF:
                $repository->persistAsPrevSiblingOf($category, $categoryNode->getTarget());
                break;

            case CategoryNode::POSITION_NEXT_SIBLING_OF:
                $repository->persistAsNextSiblingOf($category, $categoryNode->getTarget());
        }
        $this->entityManager->flush();

        $activityTransKey = null;
        if (null === $category->getId()) {
            $activityTransKey = 'activity_stream.category.created';
        } else {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($category);
            if (!empty($changeSet)) {
                $activityTransKey = 'activity_stream.category.updated';
            }
        }

        // Activity
        if (null !== $activityTransKey) {
            $activity = $this->activityManager->createFromEntities(
                $activityTransKey,
                array('%title%' => $category->getTitle()),
                $category,
                null,
                $user
            );
            $this->entityManager->persist($activity);
            $this->entityManager->flush();
        }
    }

    /**
     * Deletes a category
     *
     * @param Category $category
     */
    public function delete(Category $category)
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * Returns activities of a category
     *
     * @param Category $category
     *
     * @return array
     */
    public function getActivities(Category $category)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Activity')->findByEntities($category);
    }
}
