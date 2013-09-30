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

use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResourceCollection;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AssociationAdminResource;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\ProducerAdminResource;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\SecurityContextInterface;


/**
 * Class AdminManager
 * Global manager for administration
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class AdminManager
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
     * @var AdminResourceCollection $adminResourceCollection;
     */
    protected $adminResourceCollection;



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
        $this->adminResourceCollection = new AdminResourceCollection();
    }

    /**
     * Returns available administration resources for user (producers, associations, etc.)
     *
     * @return AdminResourceCollection
     */
    public function findAvailableAdminResources()
    {
        $producers = $this->findAvailableProducers();
        foreach ($producers as $producer) {
            $this->adminResourceCollection->add(new ProducerAdminResource($producer));
        }

        $associations = $this->findAvailableAssociations();
        foreach ($associations as $association) {
            $this->adminResourceCollection->add(new AssociationAdminResource($association));
        }

        return $this->adminResourceCollection;
    }

    /**
     * Returns available producers for user
     *
     * @return array
     */
    public function findAvailableProducers()
    {
        $repository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Producer');

        $ids = $this->filterAvailableEntitiesByPk($repository, $repository->findAllIds());
        if (empty($ids)) {
            return array();
        }

        return $repository->findById($ids);
    }

    /**
     * Returns available associations for user
     *
     * @return array
     */
    public function findAvailableAssociations()
    {
        $repository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Association');

        $ids = $this->filterAvailableEntitiesByPk($repository, $repository->findAllIds());
        if (empty($ids)) {
            return array();
        }

        return $repository->findById($ids);
    }

    /**
     * Returns available entities for user
     *
     * @param ObjectRepository $repository
     * @param array $pks
     *
     * @return array
     */
    protected function filterAvailableEntitiesByPk(ObjectRepository $repository, array $pks)
    {
        $availablePks = array();
        foreach ($pks as $pk) {
            $objectIdentity = new ObjectIdentity($pk, $repository->getClassName());
            if ($this->securityContext->isGranted('OWNER', $objectIdentity)) {
                $availablePks[] = $pk;
            }
        }

        return $availablePks;
    }
}
