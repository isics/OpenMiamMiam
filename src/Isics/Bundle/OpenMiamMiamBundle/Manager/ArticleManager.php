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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Article;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

/**
 * Class ArticleManager
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class ArticleManager
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
     * Returns a new article for a association
     *
     * @param Association $association
     *
     * @return Article
     */
    public function createForAssociation(Association $association)
    {
        $article = new Article();
        $article->setAssociation($association);

        // Select all association branches
        $article->setBranches($association->getBranches());

        return $article;
    }

    /**
     * Returns a new article for super
     *
     * @return Article
     */
    public function createForSuper()
    {
        $article = new Article();

        // Select all branches
        $article->setBranches(
            $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Branch')->findAll()
        );

        return $article;
    }

    /**
     * Saves a article
     *
     * @param Article $article
     * @param User $user
     */
    public function save(Article $article, User $user = null)
    {
        $association = $article->getAssociation();

        $activityTransKey = null;
        if (null === $article->getId()) {
            $activityTransKey = 'activity_stream.article.created';
        } else {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($article);
            if (!empty($changeSet)) {
                $activityTransKey = 'activity_stream.article.updated';
            }
        }

        // Save object
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        // Activity
        if (null !== $activityTransKey) {
            $activity = $this->activityManager->createFromEntities(
                $activityTransKey,
                array('%title%' => $article->getTitle()),
                $article,
                $association,
                $user
            );
            $this->entityManager->persist($activity);
            $this->entityManager->flush();
        }
    }

    /**
     * Deletes a article
     *
     * @param Article $article
     */
    public function delete(Article $article)
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();
    }

    /**
     * Returns activities of a article
     *
     * @param Article $article
     *
     * @return array
     */
    public function getActivities(Article $article)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Activity')->findByEntities($article, $article->getAssociation());
    }
}
