<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\Manager;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\Acl\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class UserManager
{
    /**
     * Entity manager
     *
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * Number of days after his last order, a user is considered as a customer
     *
     * @var int $lastOrderNbDaysConsideringCustomer
     */
    private $lastOrderNbDaysConsideringCustomer;

    /**
     * Acl provider
     *
     * @var AclProviderInterface $aclProvider
     */
    private $aclProvider;

    /**
     * Constructor
     *
     * @param EntityManager        $entityManager
     * @param int                  $lastOrderNbDaysConsideringCustomer
     * @param AclProviderInterface $aclProvider
     */
    public function __construct(EntityManager $entityManager, $lastOrderNbDaysConsideringCustomer, AclProviderInterface $aclProvider)
    {
        $this->entityManager = $entityManager;
        $this->lastOrderNbDaysConsideringCustomer = $lastOrderNbDaysConsideringCustomer;
        $this->aclProvider = $aclProvider;
    }

    /**
     * Find consumers for branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     *
     * @return array Consumers
     */
    public function findConsumersForBranches($branches)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamUserBundle:User')
            ->findConsumersForBranches($branches, $this->lastOrderNbDaysConsideringCustomer);
    }

    /**
     * Find mail orders open subscribers for branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     *
     * @return array Subscribers
     */
    public function findOrdersOpenNotificationSubscribersForBranches($branches)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamUserBundle:User')
            ->findOrdersOpenNotificationSubscribersForBranches($branches, $this->lastOrderNbDaysConsideringCustomer);
    }

    /**
     * Promotes a user as admin
     *
     * @param User $user
     *
     * @throws \RuntimeException
     */
    public function promoteAdmin(User $user)
    {
        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            throw new \RuntimeException(sprintf('User %s is already super admin.', $user));
        }

        $user->addRole('ROLE_ADMIN');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Demotes a user as admin
     *
     * @param User $user
     *
     * @throws \RuntimeException
     */
    public function demoteAdmin(User $user)
    {
        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            throw new \RuntimeException(sprintf('User %s is super admin. You can\'t demote him.', $user));
        }

        $user->removeRole('ROLE_ADMIN');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Returns owner of an Association or a Producer
     *
     * @param mixed $object (Association or Producer)
     *
     * @return User|null
     *
     * @throws \InvalidArgumentException
     */
    public function getOwner($object)
    {
        if (!$object instanceof Association && !$object instanceof Producer) {
            throw new \InvalidArgumentException('Object must be an instance of Association or Producer.');
        }

        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl = $this->aclProvider->findAcl($objectIdentity);
        $objectAces = $acl->getObjectAces();

        $managers = $this->entityManager->getRepository('IsicsOpenMiamMiamUserBundle:User')->findManager($object);

        foreach ($managers as $manager) {
            $securityIdentity = UserSecurityIdentity::fromAccount($manager);

            foreach($objectAces as $index => $ace) {
                if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                    if (MaskBuilder::MASK_OWNER === $ace->getMask()) {

                        return $manager;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Set a user as owner of an Association or a Producer
     *
     * @param mixed $object (Association or Producer)
     * @param User  $user
     *
     * @throws \InvalidArgumentException
     */
    public function setOwner($object, User $user)
    {
        if (!$object instanceof Association && !$object instanceof Producer) {
            throw new \InvalidArgumentException('Object must be an instance of Association or Producer.');
        }

        $objectIdentity = ObjectIdentity::fromDomainObject($object);

        try {
            $acl = $this->aclProvider->findAcl($objectIdentity);
        } catch (AclNotFoundException $e) {
            $acl = $this->aclProvider->createAcl($objectIdentity);
        }

        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $objectAceExists = false;
        foreach($acl->getObjectAces() as $index => $ace) {
            // Look if user already in ACEs
            if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                $objectAceExists = true;

                // Promotes if user not yet owner
                if (MaskBuilder::MASK_OPERATOR === $ace->getMask()) {
                    $acl->updateObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
                }

            // Deletes old owner if exists
            } else if (!$ace->getSecurityIdentity()->equals($securityIdentity) && MaskBuilder::MASK_OWNER === $ace->getMask()) {
                $acl->deleteObjectAce($index);
            };
        }

        // Creates if no user found
        if (false === $objectAceExists) {
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Returns true if user is owner of an Association or a Producer
     *
     * @param mixed $object (Association or Producer)
     * @param User  $user
     *
     * @return boolean
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function isOwner($object, User $user)
    {
        if (!$object instanceof Association && !$object instanceof Producer) {
            throw new \InvalidArgumentException('Object must be an instance of Association or Producer.');
        }

        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl = $this->aclProvider->findAcl($objectIdentity);

        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        foreach($acl->getObjectAces() as $index => $ace) {
            if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                if (MaskBuilder::MASK_OWNER === $ace->getMask()) {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Promotes a user as operator of an Association or a Producer
     *
     * @param mixed $object (Association or Producer)
     * @param User  $user
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function promoteOperator($object, User $user)
    {
        if (!$object instanceof Association && !$object instanceof Producer) {
            throw new \InvalidArgumentException('Object must be an instance of Association or Producer.');
        }

        $objectIdentity = ObjectIdentity::fromDomainObject($object);

        try {
            $acl = $this->aclProvider->findAcl($objectIdentity);
        } catch (AclNotFoundException $e) {
            $acl = $this->aclProvider->createAcl($objectIdentity);
        }

        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $objectAceExists = false;
        foreach($acl->getObjectAces() as $index => $ace) {
            if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                $objectAceExists = true;
            }
        }

        if (false === $objectAceExists) {
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OPERATOR);
            $this->aclProvider->updateAcl($acl);
        } else {
            throw new \RuntimeException(sprintf('User %s is already owner or operator of object %s.', $user, $object));
        }
    }

    /**
     * Demotes a user as operator of an Association or a Producer
     *
     * @param mixed $object (Association or Producer)
     * @param User  $user
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function demoteOperator($object, User $user)
    {
        if (!$object instanceof Association && !$object instanceof Producer) {
            throw new \InvalidArgumentException('Object must be an instance of Association or Producer.');
        }

        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl = $this->aclProvider->findAcl($objectIdentity);

        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $objectAceExists = false;
        foreach($acl->getObjectAces() as $index => $ace) {
            if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                if (MaskBuilder::MASK_OPERATOR === $ace->getMask()) {
                    $acl->deleteObjectAce($index);
                    $objectAceExists = true;
                } else {
                    throw new \RuntimeException(sprintf('User %s is owner of object %s. You can\'t demote him.', $user, $object));
                }
            }
        }

        if (false === $objectAceExists) {
            throw new \RuntimeException(sprintf('User %s is not operator of object %s.', $user, $object));
        }

        $this->aclProvider->updateAcl($acl);
    }
}
