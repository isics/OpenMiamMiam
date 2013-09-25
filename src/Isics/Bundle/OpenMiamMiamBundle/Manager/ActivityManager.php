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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Activity;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class ActivityManager
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class ActivityManager
{
    /**
     * @var SecurityContext $securityContext
     */
    protected $securityContext;

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;



    /**
     * Constructs object
     *
     * @param SecurityContextInterface $securityContext
     * @param EntityManager            $entityManager
     */
    public function __construct(SecurityContextInterface $securityContext, EntityManager $entityManager)
    {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $transKey
     * @param array $transParams
     * @param mixed $object
     * @param mixed $target
     * @param string $userName
     *
     * @throws \DomainException
     *
     * @return Activity
     */
    public function create($transKey, array $transParams = null, $object = null, $target = null, $userName = null)
    {
        $propertyAccessor = new PropertyAccessor();
        $activity = new Activity();
        $activity->setDate(new \DateTime());
        $activity->setTransKey($transKey);
        $activity->setTransParams($transParams);

        if (null !== $this->securityContext->getToken()) {
            $user = $this->securityContext->getToken()->getUser();
            if (!$user instanceof User) {
                throw new \DomainException('Invalid user.');
            }

            $activity->setUser($user);
            $activity->setUserName($user->getFirstname().' '.$user->getLastname());
        } elseif (null !== $userName) {
            $activity->setUserName($userName);
        } else {
            $activity->setUserName('System');
        }

        if (null !== $object) {
            $metadata = $this->entityManager->getClassMetadata(get_class($object));
            $identifierFieldName = $metadata->getSingleIdentifierFieldName();

            $activity->setObjectType($metadata->getName());
            $activity->setObjectId($propertyAccessor->getValue($object, $identifierFieldName));
        }
        if (null !== $target) {
            $metadata = $this->entityManager->getClassMetadata(get_class($target));
            $identifierFieldName = $metadata->getSingleIdentifierFieldName();

            $activity->setTargetType($metadata->getName());
            $activity->setTargetId($propertyAccessor->getValue($target, $identifierFieldName));
        }

        return $activity;
    }
}
