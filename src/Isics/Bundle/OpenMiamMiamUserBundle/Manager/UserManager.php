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
     * @var integer $lastOrderNbDaysConsideringCustomer
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
     * @param EntityManager $entityManager
     * @param integer       $lastOrderNbDaysConsideringCustomer
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
     * Promotes a user as association operator
     *
     * @param Association $association
     * @param User        $user
     *
     * @throws \RuntimeException
     */
    public function promoteAssociationOperator(Association $association, User $user)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($association);

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
            throw new \RuntimeException(sprintf('User %s is already owner or operator for association %s.', $user, $association));
        }
    }

    /**
     * Demotes a user as association operator
     *
     * @param Association $association
     * @param User        $user
     *
     * @throws \RuntimeException
     */
    public function demoteAssociationOperator(Association $association, User $user)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($association);
        $acl = $this->aclProvider->findAcl($objectIdentity);

        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $objectAceExists = false;
        foreach($acl->getObjectAces() as $index => $ace) {
            if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                if (MaskBuilder::MASK_OPERATOR === $ace->getMask()) {
                    $acl->deleteObjectAce($index);
                    $objectAceExists = true;
                } else {
                    throw new \RuntimeException(sprintf('User %s is owner for association %s. You can\'t demote him.', $user, $association));
                }
            }
        }

        if (false === $objectAceExists) {
            throw new \RuntimeException(sprintf('User %s is not operator for association %s.', $user, $association));
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Returnes true if user is owner of association
     *
     * @param Association $association
     * @param User        $user
     *
     * @return boolean
     */
    public function isAssociationOwner(Association $association, User $user)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($association);
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
}
