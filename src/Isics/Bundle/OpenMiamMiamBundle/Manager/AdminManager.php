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

use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResource;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResourceCollection;
use Doctrine\Common\Persistence\ObjectManager;
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
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * @var AdminResourceCollection $adminResourceCollection;
     */
    protected $adminResourceCollection;



    /**
     * Constructs object
     *
     * @param SecurityContextInterface $securityContext
     * @param ObjectManager            $objectManager
     */
    public function __construct(SecurityContextInterface $securityContext, ObjectManager $objectManager)
    {
        $this->securityContext = $securityContext;
        $this->objectManager = $objectManager;
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
            $this->adminResourceCollection->add(new AdminResource(AdminResource::TYPE_PRODUCER, $producer));
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
        return $this->findAvailableEntities($this->objectManager->getRepository('IsicsOpenMiamMiamBundle:Producer'));
    }

    /**
     * Returns available associations for user
     *
     * @return array
     */
    public function findAvailableAssociations()
    {
        return $this->findAvailableEntities($this->objectManager->getRepository('IsicsOpenMiamMiamBundle:Association'));
    }

    /**
     * Returns available entities for user
     *
     * @param ObjectRepository $repository
     *
     * @return array
     */
    protected function findAvailableEntities(ObjectRepository $repository)
    {
        $producerIds = $repository->findAllIds();
        $availableProducerIds = array();
        foreach ($producerIds as $id) {
            $objectIdentity = new ObjectIdentity($id, $repository->getClassName());
            if ($this->securityContext->isGranted('OWNER', $objectIdentity)) {
                $availableProducerIds[] = $id;
            }
        }

        if (empty($availableProducerIds)) {
            return array();
        }

        return $repository->findById($availableProducerIds);
    }
}
