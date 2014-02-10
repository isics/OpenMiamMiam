<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Subscription;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class SubscriptionManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Create subscription for association and user
     *
     * @param Association $association
     * @param User        $user
     *
     * @return Subscription
     */
    public function create(Association $association, User $user = null)
    {
        $subscription = $association->getSubscriptionForUser($user);

        if (null === $subscription) {
            $subscription = new Subscription();
            $subscription->setAssociation($association);
            $subscription->setUser($user);
            $subscription->setCredit(0.00);
        }

        $this->entityManager->persist($subscription);

        return $subscription;
    }
}