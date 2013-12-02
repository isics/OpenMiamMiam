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
use Isics\Bundle\OpenMiamMiamBundle\Twig\UserExtension;
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
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var UserExtension $userExtension
     */
    private $userExtension;


    /**
     * Constructs object
     *
     * @param EntityManager $entityManager
     * @param UserExtension $userExtension
     */
    public function __construct(EntityManager $entityManager, UserExtension $userExtension)
    {
        $this->entityManager = $entityManager;
        $this->userExtension = $userExtension;
    }

    /**
     * @param string $transKey
     * @param array $transParams
     * @param mixed $object
     * @param mixed $target
     * @param User $user
     *
     * @throws \DomainException
     *
     * @return Activity
     */
    public function createFromEntities($transKey, array $transParams = null, $object = null, $target = null, User $user = null)
    {
        $propertyAccessor = new PropertyAccessor();
        $activity = new Activity();
        $activity->setDate(new \DateTime());
        $activity->setTransKey($transKey);
        $activity->setTransParams($transParams);

        if (null !== $user) {
            $activity->setUser($user);
            $activity->setUserName($this->userExtension->formatUserIdentity($user));
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

    /**
     * @param float $number
     * @param int $decimal
     * @return string
     */
    public function formatFloatNumber($number, $decimal = 2) {
        $number = number_format($number, 2);
        $decimal += 1;
        if ('.00' === substr($number, -$decimal)) {
            return substr($number, 0, -$decimal);
        }

        return $number;
    }
}
