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

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Model\AdminResource\AdminAdminResource;
use Isics\Bundle\OpenMiamMiamBundle\Model\AdminResource\AdminResourceCollection;
use Isics\Bundle\OpenMiamMiamBundle\Model\AdminResource\AssociationAdminResource;
use Isics\Bundle\OpenMiamMiamBundle\Model\AdminResource\ProducerAdminResource;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


/**
 * Class AdminManager
 * Global manager for administration
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class AdminManager
{
    /**
     * @var AuthorizationCheckerInterface $authorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var AdminResourceCollection $adminResourceCollection;
     */
    protected $adminResourceCollection;



    /**
     * Constructs object
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManager                 $entityManager
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, EntityManager $entityManager)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;

        $this->adminResourceCollection = new AdminResourceCollection();
    }

    /**
     * Returns available administration resources for user (producers, associations, etc.)
     *
     * @return AdminResourceCollection
     */
    public function findAvailableAdminResources()
    {
        $this->lookForAdminAdminResource();
        $this->lookForAssociationAdminResources();
        $this->lookForProducerAdminResources();

        return $this->adminResourceCollection;
    }

    /**
     * Adds admin admin resource if available
     */
    protected function lookForAdminAdminResource()
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $adminAdminResource = new AdminAdminResource();

            if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
                $adminAdminResource->setOwnerPerspective(true);
            }
            $this->adminResourceCollection->add($adminAdminResource);
        }
    }

    /**
     * Adds association admin resources if available
     */
    protected function lookForAssociationAdminResources()
    {
        $repository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Association');

        foreach ($repository->findAllIds() as $pk) {
            $objectIdentity = new ObjectIdentity($pk, $repository->getClassName());

            if ($this->authorizationChecker->isGranted('OWNER', $objectIdentity)) {
                $associationAdminResource = new AssociationAdminResource($repository->findOneById($pk));
                $associationAdminResource->setOwnerPerspective(true);
                $this->adminResourceCollection->add($associationAdminResource);
            } else if ($this->authorizationChecker->isGranted('OPERATOR', $objectIdentity)) {
                $this->adminResourceCollection->add(new AssociationAdminResource($repository->findOneById($pk)));
            }
        }
    }

    /**
     * Adds producer admin resources if available
     */
    protected function lookForProducerAdminResources()
    {
        $repository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Producer');

        foreach ($repository->findAllIds() as $pk) {
            $objectIdentity = new ObjectIdentity($pk, $repository->getClassName());

            if ($this->authorizationChecker->isGranted('OWNER', $objectIdentity)) {
                $producerAdminResource = new ProducerAdminResource($repository->findOneById($pk));
                $producerAdminResource->setOwnerPerspective(true);
                $this->adminResourceCollection->add($producerAdminResource);
            } else if ($this->authorizationChecker->isGranted('OPERATOR', $objectIdentity)) {
                $this->adminResourceCollection->add(new ProducerAdminResource($repository->findOneById($pk)));
            }
        }
    }
}
